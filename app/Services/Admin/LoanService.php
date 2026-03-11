<?php
namespace App\Services\Admin;

use App\Models\{Loan, LoanInstallment, Payment, User};

class LoanService
{
    public function getPaginated(array $filters, int $perPage = 15)
    {
        $q = Loan::with(['user', 'loanProduct']);

        if (!empty($filters['status']))    $q->where('status', $filters['status']);
        if (!empty($filters['product']))   $q->where('loan_product_id', $filters['product']);
        if (!empty($filters['overdue']))   $q->where('status', 'overdue');
        if (!empty($filters['date_from'])) $q->whereDate('disbursement_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('disbursement_date', '<=', $filters['date_to']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $q->where(function ($q) use ($s) {
                $q->where('loan_number', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%"));
            });
        }

        return $q->latest()->paginate($perPage);
    }

    public function getStats(): array
    {
        return [
            'total_active'    => Loan::where('status', 'active')->count(),
            'total_overdue'   => Loan::where('status', 'overdue')->count(),
            'total_portfolio' => Loan::where('status', 'active')->sum('outstanding_balance'),
            'paid_off'        => Loan::where('status', 'paid_off')->count(),
        ];
    }

    public function adjustSchedule(Loan $loan, array $data, User $admin): void
    {
        // Recalculate remaining installments
        $remaining = $loan->installments()->where('status', 'pending')->orderBy('installment_number')->get();
        $newRate    = isset($data['interest_rate']) ? $data['interest_rate'] / 100 : $loan->interest_rate / 100;
        $newTerm    = count($remaining);

        if ($newTerm === 0) return;

        $balance = $loan->outstanding_balance;
        $monthly = $newRate > 0
            ? $balance * ($newRate * pow(1+$newRate, $newTerm)) / (pow(1+$newRate, $newTerm)-1)
            : $balance / $newTerm;

        foreach ($remaining as $idx => $inst) {
            $interest  = round($balance * $newRate, 2);
            $principal = min(round($monthly - $interest, 2), $balance);
            $balance   = max(0, round($balance - $principal, 2));

            $inst->update([
                'principal_amount'   => $principal,
                'interest_amount'    => $interest,
                'total_amount'       => $principal + $interest,
                'outstanding_amount' => $principal + $interest,
            ]);
        }
    }

    public function recordManualPayment(Loan $loan, array $data, User $admin): Payment
    {
        $payment = Payment::create([
            'payment_reference' => 'PAY-MAN-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'loan_id'           => $loan->id,
            'user_id'           => $loan->user_id,
            'amount'            => $data['amount'],
            'method'            => $data['method'],
            'status'            => 'verified',
            'notes'             => $data['notes'] ?? 'Manual payment recorded by admin',
            'is_manual'         => true,
            'verified_by'       => $admin->id,
            'verified_at'       => now(),
        ]);

        // Apply to oldest pending/overdue installment
        $installment = $loan->installments()
            ->whereIn('status', ['pending','overdue','partial'])
            ->orderBy('due_date')
            ->first();

        if ($installment) {
            $installment->update([
                'paid_amount'        => $installment->paid_amount + $data['amount'],
                'outstanding_amount' => max(0, $installment->outstanding_amount - $data['amount']),
                'paid_at'            => now(),
                'status'             => $data['amount'] >= $installment->outstanding_amount ? 'paid' : 'partial',
            ]);
            $payment->update(['installment_id' => $installment->id]);
        }

        // Update loan balance
        $loan->decrement('outstanding_balance', $data['amount']);

        // Check if fully paid
        if ($loan->fresh()->outstanding_balance <= 0) {
            $loan->update(['status' => 'paid_off']);
        }

        return $payment;
    }

    public function closeLoan(Loan $loan, string $reason, User $admin): void
    {
        $loan->update([
            'status'       => 'closed',
            'closed_at'    => now(),
            'closed_reason'=> $reason,
            'closed_by'    => $admin->id,
        ]);
    }

    public function generateAgreementPdf(Loan $loan): string
    {
        // Stub — integrate DomPDF/mPDF here
        return storage_path("app/agreements/loan_{$loan->loan_number}.pdf");
    }
}