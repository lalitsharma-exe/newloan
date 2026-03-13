<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditLog, Payment};
use App\Services\Admin\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $svc) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status','method','date_from','date_to','search']);
        return view('admin.payments.index', [
            'payments' => $this->svc->getPaginated($filters),
            'stats'    => $this->svc->getStats(),
            'filters'  => $filters,
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['loan.user','installment','verifiedBy']);
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate(['status' => 'required|in:verified,rejected', 'notes' => 'nullable|string']);
        $this->svc->verify($payment, $request->status, $request->notes, auth('admin')->user());
        AuditLog::record('payment.'.$request->status, "Payment {$payment->payment_reference} {$request->status}", $payment);
        return redirect()->route('admin.payments.show', $payment)
                         ->with('success', 'Payment '.ucfirst($request->status).'.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate(['notes' => 'nullable|string']);
        $this->svc->verify($payment, 'rejected', $request->notes, auth('admin')->user());
        AuditLog::record('payment.rejected', "Payment {$payment->payment_reference} rejected", $payment);
        return redirect()->route('admin.payments.show', $payment)->with('success', 'Payment rejected.');
    }

    public function reverse(Request $request, Payment $payment)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $oldAmount = $payment->amount;
        $payment->update(['status' => 'reversed', 'notes' => 'Reversed: '.$request->reason]);
        if ($payment->installment_id) {
            $inst    = $payment->installment;
            $newPaid = max(0, $inst->paid_amount - $oldAmount);
            $inst->update([
                'paid_amount'        => $newPaid,
                'outstanding_amount' => $inst->total_amount - $newPaid,
                'status'             => $newPaid <= 0 ? 'pending' : 'partial',
                'paid_at'            => null,
            ]);
        }
        if ($payment->loan_id) {
            $payment->loan->increment('outstanding_balance', $oldAmount);
            if ($payment->loan->status === 'paid_off') $payment->loan->update(['status' => 'active']);
        }
        AuditLog::record('payment.reversed', "Payment {$payment->payment_reference} reversed: {$request->reason}", $payment);
        return redirect()->route('admin.payments.show', $payment)->with('success', 'Payment reversed.');
    }

    public function bulkVerify(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:payments,id']);
        $count = 0;
        foreach ($request->ids as $id) {
            $payment = Payment::find($id);
            if ($payment && $payment->status === 'pending') {
                $this->svc->verify($payment, 'verified', null, auth('admin')->user());
                $count++;
            }
        }
        AuditLog::record('payment.bulk_verify', "Bulk verified {$count} payments");
        return back()->with('success', "{$count} payment(s) verified.");
    }

    public function bulkReject(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:payments,id']);
        $count = 0;
        foreach ($request->ids as $id) {
            $payment = Payment::find($id);
            if ($payment && $payment->status === 'pending') {
                $this->svc->verify($payment, 'rejected', $request->input('reason'), auth('admin')->user());
                $count++;
            }
        }
        AuditLog::record('payment.bulk_reject', "Bulk rejected {$count} payments");
        return back()->with('success', "{$count} payment(s) rejected.");
    }

    public function reconciliation(Request $request)
    {
        $date    = $request->input('date', now()->format('Y-m-d'));
        $summary = $this->svc->getReconciliation($date);
        return view('admin.payments.reconciliation', compact('summary','date'));
    }

    public function reconcile(Request $request)
    {
        $request->validate(['date' => 'required|date', 'notes' => 'nullable|string']);
        AuditLog::record('payment.reconciliation', "Reconciliation run for {$request->date}");
        return back()->with('success', "Reconciliation for {$request->date} completed.");
    }

    public function pending(Request $request)
    {
        return $this->index($request->merge(['status' => 'pending']));
    }

    public function export(Request $request)
    {
        $filters  = $request->only(['status','method','date_from','date_to']);
        $payments = $this->svc->getPaginated($filters, 9999);
        $csv = "Ref,Borrower,Loan#,Amount,Method,Status,Date\n";
        foreach ($payments as $p) {
            $csv .= implode(',', [
                $p->payment_reference,
                '"'.($p->loan->user->name ?? '—').'"',
                '"'.($p->loan->loan_number ?? '—').'"',
                $p->amount,
                $p->method,
                $p->status,
                $p->created_at->format('Y-m-d'),
            ]) . "\n";
        }
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payments-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
}