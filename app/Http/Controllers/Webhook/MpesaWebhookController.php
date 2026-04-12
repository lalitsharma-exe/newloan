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
     * Handle STK Push Repayment Callback
     */
    public function repaymentConfirmation(Request $request)
    {
        $payload = $request->json()->all();
        Log::info('M-Pesa STK Callback Received', ['payload' => $payload]);

        $stkData = $payload['Body']['stkCallback'] ?? null;
        if (!$stkData) return response()->json(['status' => 'error', 'message' => 'Invalid payload']);

        $checkoutRequestId = $stkData['CheckoutRequestID'];
        $resultCode = $stkData['ResultCode'];
        $resultDesc = $stkData['ResultDesc'];

        // Find the payment by checkout ID stored in notes or gateway_reference
        $payment = Payment::where('gateway_reference', $checkoutRequestId)
            ->orWhere('notes', 'like', "%{$checkoutRequestId}%")
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            Log::warning('M-Pesa STK Callback: No matching pending payment found', ['checkout_id' => $checkoutRequestId]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ($resultCode == 0) {
            // Success
            $this->applyPaymentToLoan($payment, $checkoutRequestId);
            Log::info('M-Pesa STK Callback: Payment VERIFIED', ['ref' => $payment->payment_reference]);
        } else {
            // Failed
            $payment->update([
                'status' => 'failed',
                'notes'  => $payment->notes . " | M-Pesa Failed: [{$resultCode}] {$resultDesc}"
            ]);
            Log::warning('M-Pesa STK Callback: Payment FAILED', ['ref' => $payment->payment_reference, 'code' => $resultCode]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Handle B2C Disbursement Result
     */
    public function disbursementResult(Request $request)
    {
        $payload = $request->json()->all();
        Log::info('M-Pesa B2C Result Received', ['payload' => $payload]);

        $result = $payload['Result'] ?? null;
        if (!$result) return response()->json(['status' => 'error']);

        $conversationId = $result['ConversationID'];
        $resultCode     = $result['ResultCode'];
        $resultDesc     = $result['ResultDesc'];

        // Find the loan by conversation ID
        $loan = Loan::where('disbursement_provider', 'like', "%M-Pesa:{$conversationId}%")
            ->first();

        if (!$loan) {
            Log::warning('M-Pesa B2C Result: No matching loan found', ['conv_id' => $conversationId]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ($resultCode == 0) {
            Log::info('M-Pesa B2C Result: Disbursement SUCCESS', ['loan' => $loan->loan_number]);
            AuditLog::record('loan.mpesa_disburse_success', "M-Pesa disbursement verified for loan {$loan->loan_number}", $loan);
        } else {
            Log::error('M-Pesa B2C Result: Disbursement FAILED', ['loan' => $loan->loan_number, 'error' => $resultDesc]);
            AuditLog::record('loan.mpesa_disburse_failed', "M-Pesa disbursement failed for loan {$loan->loan_number}: [{$resultCode}] {$resultDesc}", $loan);
            
            // Note: We might want to revert the loan status here, 
            // but currently the admin confirms it as 'active' when sending the request.
            $loan->update(['notes' => $loan->notes . " | M-Pesa Disbursement FAILED: {$resultDesc}"]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function disbursementTimeout(Request $request)
    {
        Log::warning('M-Pesa B2C Timeout Received', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function applyPaymentToLoan(Payment $payment, string $gatewayRef): void
    {
        $payment->update([
            'status'            => 'verified',
            'verified_at'       => now(),
            'gateway_reference' => $gatewayRef,
            'notes'             => $payment->notes . " | M-Pesa STK SUCCESS."
        ]);

        $loan = $payment->loan;
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

        AuditLog::record(
            'payment.mpesa_verified',
            "M-Pesa payment M{$payment->amount} verified for loan {$loan->loan_number}. TXN: {$gatewayRef}",
            $loan
        );
    }
}
