<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Referral, Loan, Payment, AuditLog};
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReferralController extends Controller
{
    public function __construct(
        private MpesaService $mpesa
    ) {}

    public function index(Request $request)
    {
        $q = Referral::with(['referrer', 'referred', 'loan']);

        if ($request->status) {
            $q->where('status', $request->status);
        }

        if ($request->search) {
            $s = $request->search;
            $q->where(function($q) use ($s) {
                $q->whereHas('referrer', fn($u) => $u->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))
                  ->orWhereHas('referred', fn($u) => $u->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
            });
        }

        $referrals = $q->latest()->paginate(20);

        $mpesaConfigured = $this->mpesa->isConfigured();

        return view('admin.referrals.index', compact('referrals', 'mpesaConfigured'));
    }

    /**
     * Option 1: Manual — Admin pays outside the system and marks it done.
     */
    public function markAsPaid(Referral $referral)
    {
        if ($referral->status !== 'qualified') {
            return back()->with('error', 'Only qualified referrals can be marked as paid.');
        }

        $referral->update([
            'status'      => 'paid',
            'paid_at'     => now(),
            'payout_method' => 'manual',
        ]);

        AuditLog::record(
            'referral.paid_manual',
            "Referral commission M{$referral->amount} for {$referral->referrer->name} marked as paid (manual).",
            null, [], ['referral_id' => $referral->id, 'referrer' => $referral->referrer->name]
        );

        return back()->with('success', "M{$referral->amount} referral commission marked as paid (manual) for {$referral->referrer->name}.");
    }

    /**
     * Option 2: Credit to Loan — Deduct M50 from referrer's active loan balance.
     */
    public function creditToLoan(Referral $referral)
    {
        if ($referral->status !== 'qualified') {
            return back()->with('error', 'Only qualified referrals can be paid.');
        }

        $referrer = $referral->referrer;
        $activeLoan = Loan::where('user_id', $referrer->id)
            ->whereIn('status', ['active', 'overdue'])
            ->latest()
            ->first();

        if (!$activeLoan) {
            return back()->with('error', "No active loan found for {$referrer->name}. Use manual payout or M-Pesa instead.");
        }

        $amount = (float) $referral->amount;

        // Apply to oldest unpaid installment first
        $installment = $activeLoan->installments()
            ->whereIn('status', ['pending', 'overdue', 'partial'])
            ->orderBy('due_date')
            ->first();

        if ($installment) {
            $apply   = min($amount, (float) $installment->outstanding_amount);
            $newPaid = round((float) $installment->paid_amount + $apply, 2);
            $newOut  = round(max(0, (float) $installment->total_amount - $newPaid), 2);
            $installment->update([
                'paid_amount'        => $newPaid,
                'outstanding_amount' => $newOut,
                'status'             => $newOut <= 0 ? 'paid' : 'partial',
                'paid_at'            => $newOut <= 0 ? now() : $installment->paid_at,
            ]);
        }

        // Reduce loan outstanding balance
        $activeLoan->decrement('outstanding_balance', $amount);
        $activeLoan->refresh();

        // Check if loan is fully paid
        if ($activeLoan->outstanding_balance <= 0 || $activeLoan->installments()->whereNotIn('status', ['paid', 'waived'])->count() === 0) {
            $activeLoan->update(['status' => 'paid_off', 'last_payment_date' => now()]);
        }

        // Record as a payment for audit trail
        Payment::create([
            'loan_id'           => $activeLoan->id,
            'user_id'           => $referrer->id,
            'amount'            => $amount,
            'method'            => 'referral_credit',
            'status'            => 'verified',
            'verified_at'       => now(),
            'payment_reference' => 'REFCR-' . $referral->id . '-' . now()->format('YmdHis'),
            'notes'             => "Referral commission credit (referred: {$referral->referred->name})",
            'installment_id'    => $installment->id ?? null,
        ]);

        $referral->update([
            'status'        => 'paid',
            'paid_at'       => now(),
            'payout_method' => 'loan_credit',
        ]);

        AuditLog::record(
            'referral.paid_loan_credit',
            "Referral M{$amount} credited to loan {$activeLoan->loan_number} for {$referrer->name}.",
            $activeLoan, [],
            ['referral_id' => $referral->id, 'loan' => $activeLoan->loan_number]
        );

        return back()->with('success', "M{$amount} credited to {$referrer->name}'s loan ({$activeLoan->loan_number}). Outstanding reduced.");
    }

    /**
     * Option 3: M-Pesa B2C — Send M50 directly to the referrer's phone.
     */
    public function payViaMpesa(Referral $referral)
    {
        if ($referral->status !== 'qualified') {
            return back()->with('error', 'Only qualified referrals can be paid.');
        }

        if (!$this->mpesa->isConfigured()) {
            return back()->with('error', 'M-Pesa is not configured. Use manual payout or loan credit instead.');
        }

        $referrer  = $referral->referrer;
        $phone     = $referrer->phone;
        $amount    = (float) $referral->amount;
        $reference = 'REFMP-' . $referral->id . '-' . now()->format('YmdHis');

        // Create a temporary Loan-like object for the M-Pesa service
        // We need to use the B2C endpoint directly
        $sessionId = $this->mpesa->getSessionID();
        if (!$sessionId) {
            return back()->with('error', 'Could not authenticate with M-Pesa. Please try again.');
        }

        try {
            $msisdn = $this->mpesa->formatPhone($phone);
            $convId = preg_replace('/[^A-Za-z0-9]/', '', $reference);

            $payload = [
                'input_Amount'                   => number_format($amount, 1, '.', ''),
                'input_Country'                  => 'LES',
                'input_Currency'                 => 'LSL',
                'input_CustomerMSISDN'           => $msisdn,
                'input_ServiceProviderCode'      => config('services.mpesa.shortcode', '000000'),
                'input_ThirdPartyConversationID' => $convId,
                'input_TransactionReference'     => substr($convId, 0, 10),
                'input_PurchasedItemsDesc'       => 'Referral Commission Payout',
            ];

            Log::info('ReferralController::payViaMpesa REQUEST', $payload);

            $encryptedKey = $this->encryptSessionId($sessionId);

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $encryptedKey,
                'Content-Type'  => 'application/json',
                'Origin'        => '*',
            ])->post($this->getMpesaBaseUrl() . '/b2cPayment/', $payload);

            Log::info('ReferralController::payViaMpesa RESPONSE', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            if ($response->successful() && ($response->json()['output_ResponseCode'] ?? '') == 'INS-0') {
                $txnId = $response->json()['output_TransactionID'] ?? null;

                $referral->update([
                    'status'        => 'paid',
                    'paid_at'       => now(),
                    'payout_method' => 'mpesa',
                ]);

                AuditLog::record(
                    'referral.paid_mpesa',
                    "Referral M{$amount} sent via M-Pesa to {$referrer->name} ({$phone}). TXN: {$txnId}",
                    null, [],
                    ['referral_id' => $referral->id, 'mpesa_txn' => $txnId]
                );

                return back()->with('success', "M{$amount} sent to {$referrer->name} via M-Pesa. TXN: {$txnId}");
            }

            $error = $response->json()['output_ResponseDesc'] ?? 'Unknown M-Pesa error';
            Log::error('ReferralController::payViaMpesa FAILED', ['error' => $error]);
            return back()->with('error', "M-Pesa payout failed: {$error}. Try manual payout or loan credit.");

        } catch (\Exception $e) {
            Log::error('ReferralController::payViaMpesa EXCEPTION', ['error' => $e->getMessage()]);
            return back()->with('error', 'M-Pesa payout error: ' . $e->getMessage());
        }
    }

    private function encryptSessionId(string $sessionId): string
    {
        $pubKey = config('services.mpesa.public_key');
        $pubKeyPem = "-----BEGIN PUBLIC KEY-----\n" .
                     wordwrap($pubKey, 64, "\n", true) .
                     "\n-----END PUBLIC KEY-----";

        $encrypted = '';
        openssl_public_encrypt($sessionId, $encrypted, $pubKeyPem, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }

    private function getMpesaBaseUrl(): string
    {
        $host   = config('services.mpesa.host', 'openapi.m-pesa.com');
        $env    = config('services.mpesa.env', 'sandbox');
        $market = config('services.mpesa.market', 'vodacomLES');
        return "https://{$host}/{$env}/ipg/v2/{$market}";
    }
}
