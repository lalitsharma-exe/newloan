<?php
namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\{Payment, AuditLog};
use App\Services\CPayService;
use App\Services\Admin\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PaymentWebhookController
 *
 * Receives async POST notifications from CPay when a transaction status changes.
 * CPay sends: PROCESSED | DENIED | CANCELED | EXPIRED | REVERSED
 *
 * This endpoint must be:
 *  - Excluded from CSRF verification (already in VerifyCsrfToken middleware or via routes)
 *  - Reachable from CPay servers (not behind auth)
 *  - Idempotent — same webhook can be sent multiple times
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        private CPayService $cpay,
        private LoanService $loanService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('CPay webhook received', ['payload' => $payload, 'ip' => $request->ip()]);

        // ── 1. Verify checksum ────────────────────────────────────────────────
        if ($this->cpay->isConfigured() && !$this->cpay->validateWebhookChecksum($payload)) {
            Log::warning('CPay webhook: invalid checksum', ['payload' => $payload]);
            return response()->json(['status' => 'invalid_checksum'], 400);
        }

        // ── 2. Parse the webhook ──────────────────────────────────────────────
        $parsed = $this->cpay->parseWebhook($payload);

        Log::info('CPay webhook parsed', $parsed);

        // ── 3. Find matching payment by our reference ─────────────────────────
        $ourRef  = $parsed['our_ref'];
        $payment = $ourRef ? Payment::where('payment_reference', $ourRef)
                                ->orWhere('gateway_reference', $parsed['cpay_txn_id'])
                                ->first()
                           : null;

        if (!$payment) {
            // Could be a disbursement webhook (no payment record for that)
            Log::info('CPay webhook: no matching payment record', ['our_ref' => $ourRef, 'cpay_txn' => $parsed['cpay_txn_id']]);
            return response()->json(['status' => 'no_match_ok']);
        }

        // ── 4. Idempotency — skip if already processed ────────────────────────
        if ($payment->status === 'verified') {
            Log::info('CPay webhook: payment already verified, skipping', ['ref' => $ourRef]);
            return response()->json(['status' => 'already_processed']);
        }

        // ── 5. Store CPay transaction ID ──────────────────────────────────────
        if ($parsed['cpay_txn_id'] && !$payment->gateway_reference) {
            $payment->update(['gateway_reference' => $parsed['cpay_txn_id']]);
        }

        // ── 6. Handle each CPay terminal status ───────────────────────────────
        if ($parsed['is_success']) {
            // PROCESSED — apply payment to loan
            $this->applyPaymentToLoan($payment, $parsed);

        } elseif ($parsed['is_reversed']) {
            // REVERSED — reverse the payment
            $payment->update([
                'status' => 'reversed',
                'notes'  => 'Reversed by CPay. TXN: ' . $parsed['cpay_txn_id'],
            ]);
            if ($payment->loan) {
                $payment->loan->increment('outstanding_balance', $payment->amount);
                if ($payment->loan->status === 'paid_off') {
                    $payment->loan->update(['status' => 'active']);
                }
            }
            AuditLog::record('payment.reversed_by_gateway', "CPay reversed payment {$payment->payment_reference}", $payment->loan);
            Log::info('CPay webhook: payment reversed', ['ref' => $ourRef]);

        } elseif ($parsed['is_failed']) {
            // DENIED / CANCELED / EXPIRED
            $payment->update([
                'status' => 'failed',
                'notes'  => "CPay status: {$parsed['status']}. TXN: {$parsed['cpay_txn_id']}",
            ]);
            AuditLog::record('payment.failed', "CPay payment failed ({$parsed['status']}): {$payment->payment_reference}", $payment->loan);
            Log::info('CPay webhook: payment failed', ['ref' => $ourRef, 'status' => $parsed['status']]);

        } else {
            // Unknown status — log and return
            Log::warning('CPay webhook: unknown status', ['status' => $parsed['status'], 'ref' => $ourRef]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function applyPaymentToLoan(Payment $payment, array $parsed): void
    {
        $payment->update([
            'status'            => 'verified',
            'verified_at'       => now(),
            'gateway_reference' => $parsed['cpay_txn_id'] ?? $payment->gateway_reference,
            'notes'             => "CPay PROCESSED. TXN: {$parsed['cpay_txn_id']}",
        ]);

        $loan = $payment->loan;
        $application = $payment->application;

        if ($application) {
            $isFee = str_starts_with($payment->payment_reference, 'APPF-');
            $isVer = str_starts_with($payment->payment_reference, 'VER-');

            if ($isFee) {
                $application->update([
                    'fee_paid' => true,
                    'fee_amount_paid' => $payment->amount,
                    'step' => max($application->step, 10)
                ]);
                Log::info('CPay webhook: marked application fee as paid', ['app_id' => $application->id]);
            } elseif ($isVer || !$application->card_tokenised) {
                $application->update([
                    'card_tokenised' => true,
                    'step' => max($application->step, 10)
                ]);
                Log::info('CPay webhook: marked application card as tokenised', ['app_id' => $application->id]);
            }
        }

        if (!$loan) return;

        // Apply to installments in order (oldest first, penalty → fees → interest → principal)
        $remaining = (float) $payment->amount;
        $installments = $loan->installments()
            ->whereIn('status', ['pending','overdue','partial'])
            ->orderBy('due_date')
            ->get();

        foreach ($installments as $inst) {
            if ($remaining <= 0) break;
            $owed = max(0, (float)$inst->total_amount - (float)$inst->paid_amount);
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
            $remaining -= $apply;
        }

        $loan->decrement('outstanding_balance', $payment->amount - $remaining);
        $loan->refresh();

        if ($loan->outstanding_balance <= 0 || $loan->installments()->whereNotIn('status',['paid','waived'])->count() === 0) {
            $loan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
        }

        AuditLog::record(
            'payment.gateway_verified',
            "CPay payment M{$payment->amount} verified for loan {$loan->loan_number}. TXN: {$parsed['cpay_txn_id']}",
            $loan
        );

        Log::info('CPay webhook: payment applied to loan', [
            'ref'       => $payment->payment_reference,
            'loan'      => $loan->loan_number,
            'amount'    => $payment->amount,
            'cpay_txn'  => $parsed['cpay_txn_id'],
        ]);
    }
}
