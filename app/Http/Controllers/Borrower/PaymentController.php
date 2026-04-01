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
        return view('borrower.payments.make', compact('loans','cpayConfigured','cpayIsSandbox'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1: Initiate payment → CPay sends OTP to user's phone (or redirects for card)
    // ─────────────────────────────────────────────────────────────────────────
    public function initiate(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount'  => 'required|numeric|min:1',
            'phone'   => 'nullable|string|max:30',
            'method'  => 'nullable|in:mobile_money,card,cpay_wallet',
        ]);

        $loan   = Loan::where('user_id', auth('borrower')->id())->findOrFail($request->loan_id);
        $user   = auth('borrower')->user();
        $phone  = $request->phone ?? $user->phone;
        $amount = (float) $request->amount;
        $method = $request->input('method', 'mobile_money');

        // Create pending payment record before any API call
        $payment = Payment::create([
            'payment_reference' => 'PAY-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'loan_id'           => $loan->id,
            'user_id'           => $user->id,
            'amount'            => $amount,
            'method'            => $method,
            'status'            => 'pending',
            'notes'             => 'CPay payment initiated via ' . $method,
        ]);


        // ── DEMO MODE: No CPay credentials ────────────────────────────────────
        if (!$this->cpay->isConfigured()) {
            $this->applyPaymentToLoan($payment);
            return redirect()->route('borrower.payments.callback.success', ['ref' => $payment->payment_reference]);
        }

        // ── LIVE: Send payment initiation to CPay ─────────────────────────────
        $result = $this->cpay->initiateRepayment($payment, $phone, $method);

        if ($result['success']) {

            // ── CARD: CPay returns "Payment Link Created" — redirect user there ─
            // CPay card quirk: HTTP 400 body StatusCode=202 → parseResponse sets success=true + is_card_link=true
            if ($result['is_card'] ?? false) {
                $redirectUrl = $result['redirect_url']
                    ?? $result['data']['redirectUrl']
                    ?? $result['data']['paymentLink']
                    ?? null;

                // Case 1: Valid URL → redirect to CPay card payment page
                if ($redirectUrl && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                    Log::info('CPay card redirect', ['url' => $redirectUrl, 'ref' => $payment->payment_reference]);
                    return redirect()->away($redirectUrl);
                }

                // Case 2: Raw HTML (CPay renders 3DS page inline)
                $html = $result['raw'] ?? null;
                if ($html && strlen($html) > 100 && str_contains($html, '<')) {
                    return response($html)->header('Content-Type', 'text/html');
                }

                // Case 3: "Payment Link Created" but no URL/HTML.
                // CPay sends link via SMS/email to the user's registered number.
                // Show status-polling page — webhook marks payment verified when card is paid.
                Log::info('CPay card: payment link created (no redirect URL), showing pending page', [
                    'ref'  => $payment->payment_reference,
                    'desc' => $result['description'] ?? $result['message'] ?? '',
                ]);
                return view('borrower.payments.cpay-pending', [
                    'payment' => $payment,
                    'message' => 'Your card payment link has been created. CPay will send it to your registered number via SMS. Click "Check Status" below once you\'ve paid.',
                ]);
            }

            // ── MOBILE / WALLET: OTP was sent — show OTP confirmation form ────
            session(['cpay_phone_' . $payment->payment_reference => $phone]);

            return view('borrower.payments.cpay-otp', [
                'payment' => $payment,
                'phone'   => $this->cpay->normalisePhone($phone),
                'message' => $result['description'] ?? $result['message'] ?? 'An OTP has been sent to your phone.',
            ]);
        }

        // CPay rejected the initiation request
        $errMsg     = $result['error'] ?? $result['description'] ?? 'Please try again.';
        $reasonCode = $result['reason_code'] ?? null;
        $fullErr    = $reasonCode ? "[{$reasonCode}] {$errMsg}" : $errMsg;

        $payment->update(['status' => 'failed', 'notes' => 'CPay error: ' . $fullErr]);
        Log::error('CPay repayment initiation failed', [
            'ref'   => $payment->payment_reference,
            'error' => $fullErr,
        ]);

        return redirect()->route('borrower.payments.make')
            ->with('error', 'Payment could not be started: ' . $fullErr);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2: Confirm payment with OTP entered by user
    // ─────────────────────────────────────────────────────────────────────────
    public function confirmOtp(Request $request)
    {
        $request->validate([
            'payment_reference' => 'required|string',
            'otp'               => 'required|string|min:4|max:10',
        ]);

        $payment = Payment::where('payment_reference', $request->payment_reference)
            ->where('user_id', auth('borrower')->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $phone = session('cpay_phone_' . $payment->payment_reference)
              ?? auth('borrower')->user()->phone;

        $result = $this->cpay->confirmPayment($payment, $phone, $request->otp);

        if ($result['success']) {
            // Clear session
            session()->forget('cpay_phone_' . $payment->payment_reference);

            // Apply payment to loan
            $this->applyPaymentToLoan($payment);

            return redirect()->route('borrower.payments.callback.success', [
                'ref' => $payment->payment_reference,
            ]);
        }

        // OTP wrong or expired
        Log::warning('CPay OTP confirmation failed', [
            'ref'   => $payment->payment_reference,
            'error' => $result['error'] ?? '',
        ]);

        return back()->with('error', 'OTP confirmation failed: ' . ($result['error'] ?? 'Invalid or expired OTP. Please try again.'))
            ->withInput();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Callbacks
    // ─────────────────────────────────────────────────────────────────────────
    public function callbackSuccess(Request $request)
    {
        $ref     = $request->input('ref') ?? $request->input('transactionId');
        $payment = Payment::where('payment_reference', $ref)
            ->where('user_id', auth('borrower')->id())
            ->first();

        if (!$payment) {
            return redirect()->route('borrower.payments.index')->with('error', 'Payment not found.');
        }

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
     * AJAX status poll (for webhook-based flows)
     */
    public function checkStatus(Request $request)
    {
        $request->validate(['ref' => 'required|string']);
        $payment = Payment::where('payment_reference', $request->ref)
            ->where('user_id', auth('borrower')->id())
            ->first();

        if (!$payment) return response()->json(['status' => 'not_found'], 404);

        $payment->refresh();
        return response()->json([
            'status'       => $payment->status,
            'amount'       => 'M' . number_format($payment->amount, 2),
            'reference'    => $payment->payment_reference,
            'redirect_url' => $payment->status === 'verified'
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
    private function applyPaymentToLoan(Payment $payment): void
    {
        if ($payment->status === 'verified') return;

        $payment->update(['status' => 'verified', 'verified_at' => now()]);

        $loan = $payment->loan;
        if (!$loan) return;

        $remaining    = (float) $payment->amount;
        $installments = $loan->installments()
            ->whereIn('status', ['pending','overdue','partial'])
            ->orderBy('due_date')
            ->get();

        foreach ($installments as $inst) {
            if ($remaining <= 0) break;
            $owed    = max(0, (float)$inst->total_amount - (float)$inst->paid_amount);
            if ($owed <= 0) continue;
            $apply   = min($remaining, $owed);
            $newPaid = round((float)$inst->paid_amount + $apply, 2);
            $newOut  = round(max(0, (float)$inst->total_amount - $newPaid), 2);
            $inst->update([
                'paid_amount'        => $newPaid,
                'outstanding_amount' => $newOut,
                'status'             => $newOut <= 0 ? 'paid' : 'partial',
                'paid_at'            => $newOut <= 0 ? now() : $inst->paid_at,
            ]);
            if (!$payment->installment_id) {
                $payment->update(['installment_id' => $inst->id]);
            }
            $remaining -= $apply;
        }

        $loan->decrement('outstanding_balance', (float)$payment->amount - $remaining);
        $loan->refresh();

        if ($loan->outstanding_balance <= 0 ||
            $loan->installments()->whereNotIn('status', ['paid','waived'])->count() === 0) {
            $loan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
        }
    }
}
