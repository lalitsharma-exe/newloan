<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\{Payment, Loan, AuditLog};
use App\Services\Admin\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaWebhookController extends Controller
{
    public function __construct(
        private LoanService $loanService
    ) {}

    /**
     * Handle C2B/STK Callback (Repayment)
     */
    public function repaymentConfirmation(Request $request)
    {
        $payload = $request->json()->all();
        Log::info('Vodacom M-Pesa Repayment Callback Received', ['payload' => $payload]);

        $resCode  = $payload['output_ResponseCode'] ?? null;
        $resDesc  = $payload['output_ResponseDesc'] ?? 'No description';
        $ref      = $payload['output_ThirdPartyConversationID'] ?? null;
        $mpesaRef = $payload['output_TransactionID'] ?? 'Unknown';

        if (!$ref) return response()->json(['output_ResponseCode' => 'INS-988', 'output_ResponseDesc' => 'Missing Reference']);

        $payment = Payment::where('payment_reference', $ref)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            Log::warning('Vodacom M-Pesa Callback: No matching pending payment found', ['ref' => $ref]);
            return response()->json(['output_ResponseCode' => 'INS-0', 'output_ResponseDesc' => 'Acknowledged']);
        }

        if ($resCode === 'INS-0') {
            // Success
            $this->applyPaymentToLoan($payment, $mpesaRef);
            Log::info('Vodacom M-Pesa Callback: Payment VERIFIED', ['ref' => $payment->payment_reference]);
        } else {
            // Failed
            $payment->update([
                'status' => 'failed',
                'notes'  => $payment->notes . " | Vodacom Failed: [{$resCode}] {$resDesc}"
            ]);
            Log::warning('Vodacom M-Pesa Callback: Payment FAILED', ['ref' => $payment->payment_reference, 'code' => $resCode]);
        }

        return response()->json(['output_ResponseCode' => 'INS-0', 'output_ResponseDesc' => 'Acknowledged']);
    }

    /**
     * Handle B2C Disbursement Result
     */
    public function disbursementResult(Request $request)
    {
        $payload = $request->json()->all();
        Log::info('Vodacom M-Pesa B2C Result Received', ['payload' => $payload]);

        $resCode  = $payload['output_ResponseCode'] ?? null;
        $resDesc  = $payload['output_ResponseDesc'] ?? 'No description';
        $ref      = $payload['output_ThirdPartyConversationID'] ?? null;
        $mpesaRef = $payload['output_TransactionID'] ?? 'Unknown';

        $loan = Loan::where('disbursement_provider', 'like', "%M-Pesa:{$ref}%")
            ->first();

        if (!$loan) {
            Log::warning('Vodacom M-Pesa B2C Result: No matching loan found', ['ref' => $ref]);
            return response()->json(['output_ResponseCode' => 'INS-0', 'output_ResponseDesc' => 'Acknowledged']);
        }

        if ($resCode === 'INS-0') {
            Log::info('Vodacom M-Pesa B2C Result: Disbursement SUCCESS', ['loan' => $loan->loan_number]);
            AuditLog::record('loan.mpesa_disburse_success', "M-Pesa disbursement verified for loan {$loan->loan_number}. M-Pesa Ref: {$mpesaRef}", $loan);
        } else {
            Log::error('Vodacom M-Pesa B2C Result: Disbursement FAILED', ['loan' => $loan->loan_number, 'error' => $resDesc]);
            AuditLog::record('loan.mpesa_disburse_failed', "M-Pesa disbursement failed for loan {$loan->loan_number}: [{$resCode}] {$resDesc}", $loan);
            
            $loan->update(['notes' => $loan->notes . " | Vodacom Disbursement FAILED: {$resDesc}"]);
        }

        return response()->json(['output_ResponseCode' => 'INS-0', 'output_ResponseDesc' => 'Acknowledged']);
    }

    public function disbursementTimeout(Request $request)
    {
        Log::warning('Vodacom M-Pesa B2C Timeout Received', $request->all());
        return response()->json(['output_ResponseCode' => 'INS-0', 'output_ResponseDesc' => 'Acknowledged']);
    }

    private function applyPaymentToLoan(Payment $payment, string $gatewayRef): void
    {
        $payment->update([
            'status'            => 'verified',
            'verified_at'       => now(),
            'gateway_reference' => $gatewayRef,
            'notes'             => $payment->notes . " | Vodacom M-Pesa SUCCESS."
        ]);

        $loan = $payment->loan;
        $application = $payment->application;

        if ($application) {
            $isFee = str_starts_with($payment->payment_reference, 'APPF-');
            if ($isFee) {
                $application->update([
                    'fee_paid' => true,
                    'fee_amount_paid' => $payment->amount,
                    'step' => max($application->step, 10)
                ]);
                Log::info('M-Pesa webhook: marked application fee as paid', ['app_id' => $application->id]);
            }
        }

        if (!$loan) return;

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

        // Referral System: Check if this payment qualifies a referral
        $this->loanService->checkReferralQualification($loan);

        AuditLog::record(
            'payment.mpesa_verified',
            "M-Pesa payment LSL{$payment->amount} verified for loan {$loan->loan_number}. TXN: {$gatewayRef}",
            $loan
        );
    }
}
