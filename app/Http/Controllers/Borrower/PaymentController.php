<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\{Payment, Loan, LoanInstallment};
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index() {
        $payments = Payment::where('user_id', auth('borrower')->id())->with(['loan.loanProduct'])->latest()->paginate(15);
        return view('borrower.payments.index', compact('payments'));
    }

    public function show(Payment $payment) {
        abort_if($payment->user_id !== auth('borrower')->id(), 403);
        $payment->load(['loan.loanProduct']);
        return view('borrower.payments.show', compact('payment'));
    }

    public function showMakePayment() {
        $loans = Loan::where('user_id', auth('borrower')->id())->whereIn('status',['active','overdue'])->with(['installments','loanProduct'])->get();
        return view('borrower.payments.make', compact('loans'));
    }

    public function initiate(Request $request) {
        $request->validate(['loan_id' => 'required|exists:loans,id', 'amount' => 'required|numeric|min:1']);
        $loan = Loan::where('user_id', auth('borrower')->id())->findOrFail($request->loan_id);
        $payment = Payment::create([
            'payment_reference' => 'PAY-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'loan_id'           => $loan->id,
            'user_id'           => auth('borrower')->id(),
            'amount'            => $request->amount,
            'method'            => $request->method ?? 'mobile_money',
            'status'            => 'pending',
            'notes'             => 'Self-service payment via portal',
        ]);
        // In production: redirect to payment gateway
        return redirect()->route('borrower.payments.callback.success', ['ref' => $payment->payment_reference]);
    }

    public function callbackSuccess(Request $request) {
        $ref     = $request->input('ref');
        $payment = Payment::where('payment_reference', $ref)->where('user_id', auth('borrower')->id())->first();
        if ($payment && $payment->status === 'pending') {
            $payment->update(['status' => 'verified', 'verified_at' => now()]);
            // Apply to loan installments (reuse LoanService logic)
            $loan = $payment->loan;
            $loan->decrement('outstanding_balance', $payment->amount);
            $installments = $loan->installments()->whereIn('status',['pending','overdue','partial'])->orderBy('due_date')->get();
            $rem = (float) $payment->amount;
            foreach ($installments as $inst) {
                if ($rem <= 0) break;
                $pay = min($rem, (float)$inst->outstanding_amount);
                $newPaid = round((float)$inst->paid_amount + $pay, 2);
                $newOut  = round((float)$inst->total_amount - $newPaid, 2);
                $inst->update(['paid_amount'=>$newPaid,'outstanding_amount'=>max(0,$newOut),'status'=>$newOut<=0?'paid':'partial','paid_at'=>$newOut<=0?now():$inst->paid_at]);
                $rem -= $pay;
            }
        }
        return view('borrower.payments.callback-success', compact('payment'));
    }

    public function callbackCancel()  { return redirect()->route('borrower.payments.make')->with('info', 'Payment cancelled.'); }
    public function callbackFailed()  { return view('borrower.payments.callback-failed'); }

    public function receipt(Payment $payment) {
        abort_if($payment->user_id !== auth('borrower')->id(), 403);
        $payment->load(['loan.loanProduct']);
        return view('borrower.payments.receipt', compact('payment'));
    }
}
