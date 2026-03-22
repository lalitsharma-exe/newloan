<?php
namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\{Payment, Loan, LoanInstallment};
use App\Services\CPayService;
use App\Services\Admin\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private CPayService $cpay,
        private LoanService $loanService
    ) {}

    public function index()
    {
        $payments = Payment::where('user_id', auth('borrower')->id())
            ->with(['loan.loanProduct'])->latest()->paginate(15);
        return view('borrower.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        abort_if($payment->user_id !== auth('borrower')->id(), 403);
        $payment->load(['loan.loanProduct']);
        return view('borrower.payments.show', compact('payment'));
    }

    public function showMakePayment()
    {
        $loans = Loan::where('user_id', auth('borrower')->id())
            ->whereIn('status', ['active','overdue'])
            ->with(['installments','loanProduct'])
            ->get();
        $cpayConfigured = $this->cpay->isConfigured();
        $cpayIsSandbox  = $this->cpay->isSandbox();
        return view('borrower.payments.make', compact('loans', 'cpayConfigured', 'cpayIsSandbox'));
    }

    /**
     * Initiate payment:
     *  - If CPay configured → call CPay API, redirect to CPay payment page or await OTP
     *  - If not configured  → record pending payment (demo mode)
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount'  => 'required|numeric|min:1',
            'method'  => 'required|in:mobile_money,card,cpay_wallet,bank_transfer',
            'phone'   => 'nullable|string|max:30',
        ]);

        $loan   = Loan::where('user_id', auth('borrower')->id())->findOrFail($request->loan_id);
        $user   = auth('borrower')->user();
        $phone  = $request->phone ?? $user->phone;
        $amount = (float) $request->amount;
        $method = $request->method;

        // Create pending payment record
        $payment = Payment::create([
            'payment_reference' => 'PAY-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'loan_id'           => $loan->id,
            'user_id'           => $user->id,
            'amount'            => $amount,
            'method'            => $method,
            'status'            => 'pending',
            'notes'             => 'Self-service payment via borrower portal',
        ]);

        // ── Live CPay flow ────────────────────────────────────────────────────
        if ($this->cpay->isConfigured()) {
            $result = $this->cpay->initiateRepayment($payment, $phone, $method);

            if ($result['success']) {
                // Store CPay transaction ID for status polling / webhook matching
                $payment->update(['gateway_reference' => $result['cpay_txn_id']]);

                // If CPay returned a redirect URL (card/hosted page), send borrower there
                if (!empty($result['redirect_url'])) {
                    return redirect()->away($result['redirect_url']);
                }

                // For USSD/OTP flow — show waiting page
                return view('borrower.payments.cpay-pending', compact('payment', 'result'))
                    ->with('info', 'Please check your phone and confirm the payment via USSD or OTP.');
            }

            // CPay failed to initiate — let borrower know
            $payment->update(['status' => 'failed', 'notes' => 'CPay initiation failed: ' . ($result['error'] ?? 'unknown')]);
            Log::error('CPay repayment initiation failed', ['payment' => $payment->payment_reference, 'error' => $result['error'] ?? '']);
            return redirect()->route('borrower.payments.failed')
                ->with('error', 'Payment could not be initiated. Please try again or contact support. Error: ' . ($result['error'] ?? 'Unknown error'));
        }

        // ── Demo / sandbox fallback (no CPay credentials) ────────────────────
        // Auto-approve for testing purposes
        $this->applyPaymentToLoan($payment);
        return redirect()->route('borrower.payments.callback.success', ['ref' => $payment->payment_reference]);
    }

    /**
     * CPay callback — success (GET from CPay redirect or our webhook applied it already)
     */
    public function callbackSuccess(Request $request)
    {
        $ref     = $request->input('ref') ?? $request->input('transactionId');
        $payment = Payment::where('payment_reference', $ref)
            ->where('user_id', auth('borrower')->id())
            ->first();

        if (!$payment) {
            return redirect()->route('borrower.payments.index')->with('error', 'Payment not found.');
        }

        // If still pending (webhook may not have fired yet) — check CPay
        if ($payment->status === 'pending' && $payment->gateway_reference && $this->cpay->isConfigured()) {
            $status = $this->cpay->queryPaymentStatus($payment->gateway_reference);
            if (($status['status'] ?? '') === 'PROCESSED') {
                $this->applyPaymentToLoan($payment);
            }
        }

        // If still pending (no gateway or gateway hasn't confirmed) — apply optimistically
        if ($payment->status === 'pending') {
            $this->applyPaymentToLoan($payment);
        }

        $payment->load(['loan.loanProduct']);
        return view('borrower.payments.callback-success', compact('payment'));
    }

    public function callbackCancel()
    {
        return redirect()->route('borrower.payments.make')->with('info', 'Payment cancelled.');
    }

    public function callbackFailed()
    {
        return view('borrower.payments.callback-failed');
    }

    /**
     * Status polling endpoint — borrower JS polls this while waiting for OTP/USSD confirmation
     */
    public function checkStatus(Request $request)
    {
        $request->validate(['ref' => 'required|string']);
        $payment = Payment::where('payment_reference', $request->ref)
            ->where('user_id', auth('borrower')->id())
            ->first();

        if (!$payment) return response()->json(['status' => 'not_found'], 404);

        // If still pending, poll CPay
        if ($payment->status === 'pending' && $payment->gateway_reference && $this->cpay->isConfigured()) {
            $result = $this->cpay->queryPaymentStatus($payment->gateway_reference);
            $cpayStatus = $result['status'] ?? 'UNKNOWN';

            if ($cpayStatus === 'PROCESSED') {
                $this->applyPaymentToLoan($payment);
            } elseif (in_array($cpayStatus, ['DENIED','CANCELED','EXPIRED'])) {
                $payment->update(['status' => 'failed', 'notes' => "CPay status: {$cpayStatus}"]);
            }
        }

        $payment->refresh();
        return response()->json([
            'status'        => $payment->status,
            'amount'        => $payment->amount,
            'reference'     => $payment->payment_reference,
            'redirect_url'  => $payment->status === 'verified'
                ? route('borrower.payments.callback.success', ['ref' => $payment->payment_reference])
                : null,
        ]);
    }

    public function receipt(Payment $payment)
    {
        abort_if($payment->user_id !== auth('borrower')->id(), 403);
        $payment->load(['loan.loanProduct']);
        return view('borrower.payments.receipt', compact('payment'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INTERNAL — apply a verified payment to the loan installments
    // ─────────────────────────────────────────────────────────────────────────
    private function applyPaymentToLoan(Payment $payment): void
    {
        if ($payment->status === 'verified') return; // already applied

        $payment->update(['status' => 'verified', 'verified_at' => now()]);

        $loan = $payment->loan;
        if (!$loan) return;

        // Reuse the full allocation logic from LoanService
        $this->loanService->recordManualPayment($loan, [
            'amount' => $payment->amount,
            'method' => $payment->method,
            'notes'  => 'Applied from CPay callback/webhook',
        ], (object)['id' => null]); // null admin = system

        // Mark as paid_off if fully cleared
        $loan->refresh();
        if ($loan->outstanding_balance <= 0 || $loan->installments()->whereNotIn('status',['paid','waived'])->count() === 0) {
            $loan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
        }
    }
}
