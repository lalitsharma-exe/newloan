<?php
namespace App\Services\Admin;

use App\Models\{Payment, LoanInstallment, User};

class PaymentService
{
    public function getPaginated(array $f, int $perPage = 20)
    {
        $q = Payment::with(['loan.user','installment']);
        if (!empty($f['status']))    $q->where('status', $f['status']);
        if (!empty($f['method']))    $q->where('method', $f['method']);
        if (!empty($f['date_from'])) $q->whereDate('created_at', '>=', $f['date_from']);
        if (!empty($f['date_to']))   $q->whereDate('created_at', '<=', $f['date_to']);
        if (!empty($f['search'])) {
            $s = $f['search'];
            $q->where(fn($x) => $x->where('payment_reference', 'like', "%$s%")
                ->orWhereHas('loan.user', fn($u) => $u->where('name', 'like', "%$s%")));
        }
        return $perPage >= 9999 ? $q->latest()->get() : $q->latest()->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total_today'       => Payment::whereDate('created_at', today())->where('status','verified')->sum('amount'),
            'total_month'       => Payment::whereMonth('created_at', now()->month)->where('status','verified')->sum('amount'),
            'pending_count'     => Payment::where('status','pending')->count(),
            'total_count_month' => Payment::whereMonth('created_at', now()->month)->count(),
        ];
    }

    public function verify(Payment $payment, string $status, ?string $notes, User $admin): void
    {
        $payment->update([
            'status'      => $status,
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'notes'       => $notes,
        ]);
        if ($status === 'verified' && $payment->installment_id) {
            $inst    = $payment->installment;
            $newPaid = $inst->paid_amount + $payment->amount;
            $isPaid  = $newPaid >= $inst->total_amount;
            $inst->update([
                'paid_amount'        => $newPaid,
                'outstanding_amount' => max(0, $inst->total_amount - $newPaid),
                'status'             => $isPaid ? 'paid' : 'partial',
                'paid_at'            => $isPaid ? now() : null,
            ]);
            
            if ($payment->loan) {
                $loan = $payment->loan;
                $loan->decrement('outstanding_balance', $payment->amount);
                $loan->refresh();

                // Check if loan is now fully paid
                if ($loan->outstanding_balance <= 0 || 
                    $loan->installments()->whereNotIn('status', ['paid','waived'])->count() === 0) {
                    
                    if ($loan->status !== 'paid_off') {
                        $loan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
                    }

                    // Trigger Fully Paid SMS
                    if (!$loan->fully_paid_notified_at && $loan->user) {
                        try {
                            $loan->user->notify(new \App\Notifications\LoanFullyPaidSms($loan));
                            $loan->update(['fully_paid_notified_at' => now()]);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to send fully paid SMS for loan {$loan->id} during admin verify: " . $e->getMessage());
                        }
                    }
                }

                // Trigger Payment Receipt SMS (even if not fully paid)
                if ($loan->user) {
                    try {
                        $loan->user->notify(new \App\Notifications\PaymentReceivedSms($payment));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send receipt SMS for verified payment {$payment->payment_reference}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    public function getReconciliation(string $date): array
    {
        $payments = Payment::whereDate('created_at', $date)->with('loan.user')->get();
        return [
            'date'              => $date,
            'total_received'    => $payments->where('status','verified')->sum('amount'),
            'total_pending'     => $payments->where('status','pending')->sum('amount'),
            'total_rejected'    => $payments->where('status','rejected')->sum('amount'),
            'total_reversed'    => $payments->where('status','reversed')->sum('amount'),
            'count_verified'    => $payments->where('status','verified')->count(),
            'count_pending'     => $payments->where('status','pending')->count(),
            'count_rejected'    => $payments->where('status','rejected')->count(),
            'payments'          => $payments,
            'by_method'         => $payments->where('status','verified')->groupBy('method')
                                       ->map(fn($g) => ['count'=>$g->count(),'total'=>$g->sum('amount')])->toArray(),
        ];
    }
}