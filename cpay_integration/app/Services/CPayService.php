<?php

namespace App\Services;

use App\Models\{Loan, Payment, SystemSetting};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * CPayService — Chaperone Payments API v1.1 integration
 *
 * Covers:
 *  - Disbursements  → POST /api/disbursements/external-payment  (push money to borrower)
 *  - Repayments     → POST /api/payments/initiate               (borrower pays installment)
 *  - Status check   → GET  /api/payments/status/{txnId}
 *  - User verify    → GET  /api/disbursements/user-verification
 *  - Wallet top-up  → POST /api/disbursements/wallet-topup-advance
 *  - Webhook verify → validateWebhookChecksum()
 *
 * All amounts are in LSL (Lesotho Loti). CPay uses integer cents internally,
 * but the API accepts decimal amounts — we send as float.
 */
class CPayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $clientCode;
    private string $secretKey;
    private bool   $sandbox;

    public function __construct()
    {
        $this->sandbox    = SystemSetting::get('gateway_mode', 'sandbox') !== 'production';
        $this->baseUrl    = $this->sandbox
            ? 'https://cpay-uat-env.chaperone.co.ls:5100'
            : (SystemSetting::get('gateway_live_url', 'https://api.chaperone.co.ls'));
        $this->apiKey     = SystemSetting::get('gateway_key', config('services.cpay.api_key', ''));
        $this->clientCode = SystemSetting::get('cpay_client_code', config('services.cpay.client_code', ''));
        $this->secretKey  = SystemSetting::get('gateway_secret', config('services.cpay.secret_key', ''));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DISBURSEMENTS — send money to borrower
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verify that a CPay user account exists and is active before disbursing.
     * Endpoint: GET /api/disbursements/user-verification?phoneNumber={phone}
     */
    public function verifyUser(string $phone): array
    {
        try {
            $response = $this->client()->get('/api/disbursements/user-verification', [
                'phoneNumber' => $this->normalisePhone($phone),
            ]);

            Log::info('CPay::verifyUser', [
                'phone'  => $phone,
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json('message') ?? 'User verification failed'];
        } catch (\Throwable $e) {
            Log::error('CPay::verifyUser exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Disburse loan via CPay Wallet (borrower has CPay account).
     * Endpoint: POST /api/disbursements/deposit
     */
    public function disburseToWallet(Loan $loan, string $phone, string $reference): array
    {
        $txnId  = $this->generateTxnId($reference);
        $amount = (float) $loan->principal_amount;

        $payload = [
            'clientCode'      => $this->clientCode,
            'transactionId'   => $txnId,
            'amount'          => $amount,
            'phoneNumber'     => $this->normalisePhone($phone),
            'description'     => "Loan disbursement {$loan->loan_number}",
            'reference'       => $reference,
            'checksum'        => $this->checksum($txnId, $amount),
        ];

        return $this->postRequest('/api/disbursements/deposit', $payload, 'disburseToWallet');
    }

    /**
     * Disburse loan via External Payment (mobile money / bank transfer).
     * Endpoint: POST /api/disbursements/external-payment
     *
     * This is the main disbursement endpoint — works for:
     *   - Mobile Money (M-Pesa, CPay wallet)
     *   - Bank Transfer (EFT)
     */
    public function disburseExternal(Loan $loan, string $phone, string $provider, string $reference): array
    {
        $txnId  = $this->generateTxnId($reference);
        $amount = (float) $loan->principal_amount;

        $payload = [
            'clientCode'      => $this->clientCode,
            'transactionId'   => $txnId,
            'amount'          => $amount,
            'phoneNumber'     => $this->normalisePhone($phone),
            'provider'        => strtoupper($provider), // 'MPESA', 'CPAY', 'EFT'
            'description'     => "Loan disbursement — {$loan->loan_number} — {$loan->user->name}",
            'reference'       => $reference,
            'checksum'        => $this->checksum($txnId, $amount),
            'redirectUrl'     => route('webhooks.payment'), // push notification
            'asyncMode'       => true,
        ];

        return $this->postRequest('/api/disbursements/external-payment', $payload, 'disburseExternal');
    }

    /**
     * Disburse via Wallet Top-Up Advance (KYC verified + optional mobile money transfer).
     * Endpoint: POST /api/disbursements/wallet-topup-advance
     */
    public function disburseWalletTopup(Loan $loan, string $phone, string $reference): array
    {
        $txnId  = $this->generateTxnId($reference);
        $amount = (float) $loan->principal_amount;

        $payload = [
            'clientCode'      => $this->clientCode,
            'transactionId'   => $txnId,
            'amount'          => $amount,
            'phoneNumber'     => $this->normalisePhone($phone),
            'description'     => "Loan disbursement {$loan->loan_number}",
            'reference'       => $reference,
            'checksum'        => $this->checksum($txnId, $amount),
            'kycVerified'     => true,
        ];

        return $this->postRequest('/api/disbursements/wallet-topup-advance', $payload, 'disburseWalletTopup');
    }

    /**
     * Check disbursement / payment status.
     * Endpoint: GET /api/disbursements/payment-inquiry?phoneNumber={}&transactionId={}
     */
    public function checkDisbursementStatus(string $txnId, string $phone): array
    {
        try {
            $response = $this->client()->get('/api/disbursements/payment-inquiry', [
                'transactionId' => $txnId,
                'phoneNumber'   => $this->normalisePhone($phone),
            ]);

            return [
                'success' => $response->successful(),
                'data'    => $response->json(),
                'status'  => $response->json('status') ?? $response->json('transactionStatus') ?? 'UNKNOWN',
            ];
        } catch (\Throwable $e) {
            Log::error('CPay::checkDisbursementStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REPAYMENTS — borrower paying an installment
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Initiate a repayment via CPay.
     * This creates the transaction and returns a redirect URL or OTP flow.
     *
     * CPay supports:
     *   - asyncMode = true  → returns immediately, final status via webhook/pull
     *   - redirectUrl       → CPay calls this when done
     *   - OTP / USSD confirmation on the borrower's phone
     */
    public function initiateRepayment(
        Payment $payment,
        string  $phone,
        string  $method = 'mobile_money'   // mobile_money | card | cpay_wallet
    ): array {
        $txnId  = $payment->payment_reference;
        $amount = (float) $payment->amount;

        $provider = match ($method) {
            'card'         => 'CARD',
            'cpay_wallet'  => 'CPAY',
            default        => 'MPESA',
        };

        $payload = [
            'clientCode'      => $this->clientCode,
            'transactionId'   => $txnId,
            'amount'          => $amount,
            'phoneNumber'     => $this->normalisePhone($phone),
            'provider'        => $provider,
            'description'     => "Loan repayment — {$payment->loan->loan_number}",
            'reference'       => $payment->loan->loan_number,
            'checksum'        => $this->checksum($txnId, $amount),
            'redirectUrl'     => url(route('borrower.payments.callback.success', ['ref' => $txnId])),
            'cancelUrl'       => url(route('borrower.payments.callback.cancel')),
            'webhookUrl'      => url(route('webhooks.payment')),
            'asyncMode'       => true,
        ];

        Log::info('CPay::initiateRepayment', ['txnId' => $txnId, 'amount' => $amount, 'provider' => $provider]);

        return $this->postRequest('/api/payments/initiate', $payload, 'initiateRepayment');
    }

    /**
     * Query status of a repayment transaction.
     */
    public function queryPaymentStatus(string $cpayTxnId): array
    {
        try {
            $response = $this->client()->get("/api/payments/status/{$cpayTxnId}");
            return [
                'success' => $response->successful(),
                'status'  => $response->json('transactionStatus') ?? $response->json('status') ?? 'UNKNOWN',
                'data'    => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('CPay::queryPaymentStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'ERROR', 'error' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WEBHOOK VERIFICATION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate an incoming CPay webhook payload using HMAC-SHA256 checksum.
     *
     * CPay sends: transactionId + amount + status + secret → HMAC-SHA256
     */
    public function validateWebhookChecksum(array $payload): bool
    {
        if (empty($payload['checksum'])) return false;

        $expected = $this->checksum(
            $payload['transactionId'] ?? '',
            (float) ($payload['amount'] ?? 0),
            $payload['status'] ?? ''
        );

        return hash_equals($expected, $payload['checksum']);
    }

    /**
     * Parse a CPay webhook/callback payload into a normalised array.
     * CPay terminal statuses: PROCESSED | DENIED | CANCELED | EXPIRED | REVERSED
     */
    public function parseWebhook(array $payload): array
    {
        $status = strtoupper($payload['transactionStatus'] ?? $payload['status'] ?? '');

        return [
            'cpay_txn_id'    => $payload['cPayTransactionId'] ?? $payload['transactionId'] ?? null,
            'our_ref'        => $payload['transactionId'] ?? $payload['reference'] ?? null,
            'amount'         => (float) ($payload['amount'] ?? 0),
            'status'         => $status,
            'is_success'     => $status === 'PROCESSED',
            'is_failed'      => in_array($status, ['DENIED', 'CANCELED', 'EXPIRED']),
            'is_reversed'    => $status === 'REVERSED',
            'phone'          => $payload['phoneNumber'] ?? null,
            'provider'       => $payload['provider'] ?? null,
            'raw'            => $payload,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * HMAC-SHA256 checksum: sha256(transactionId + amount + [status] + secretKey)
     */
    public function checksum(string $txnId, float $amount, string $status = ''): string
    {
        $data = $txnId . number_format($amount, 2, '.', '') . $status . $this->secretKey;
        return hash_hmac('sha256', $data, $this->secretKey);
    }

    /**
     * Generate a unique transaction ID prefixed with our loan reference.
     */
    private function generateTxnId(string $prefix = ''): string
    {
        $safe = preg_replace('/[^A-Za-z0-9]/', '', $prefix);
        return strtoupper(substr($safe, 0, 10)) . '-' . strtoupper(Str::random(8)) . '-' . time();
    }

    /**
     * Normalise phone to international format +266XXXXXXXX
     */
    public function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 8)                                       return '+266' . $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '266'))  return '+' . $digits;
        if (strlen($digits) === 11 && str_starts_with($digits, '266'))  return '+' . $digits;
        if (str_starts_with($phone, '+266'))                             return $phone;
        return '+266' . substr($digits, -8);
    }

    /**
     * Is the gateway configured with real credentials?
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->clientCode) && !empty($this->secretKey);
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(30)
            ->retry(2, 500);
    }

    private function postRequest(string $endpoint, array $payload, string $context): array
    {
        try {
            $response = $this->client()->post($endpoint, $payload);

            Log::info("CPay::{$context}", [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->json(),
                'sandbox'  => $this->sandbox,
            ]);

            if ($response->successful()) {
                $data   = $response->json();
                $txnStatus = strtoupper($data['transactionStatus'] ?? $data['status'] ?? 'PENDING');

                return [
                    'success'      => true,
                    'cpay_txn_id'  => $data['cPayTransactionId'] ?? $data['transactionId'] ?? null,
                    'status'       => $txnStatus,
                    'redirect_url' => $data['redirectUrl'] ?? $data['paymentUrl'] ?? null,
                    'message'      => $data['message'] ?? 'Transaction initiated',
                    'data'         => $data,
                ];
            }

            $error = $response->json('message') ?? $response->json('error') ?? "HTTP {$response->status()}";
            Log::warning("CPay::{$context} failed", ['endpoint' => $endpoint, 'error' => $error, 'body' => $response->body()]);

            return ['success' => false, 'error' => $error, 'status' => 'FAILED', 'http_status' => $response->status()];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("CPay::{$context} connection error", ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Cannot connect to CPay gateway. ' . $e->getMessage(), 'status' => 'ERROR'];
        } catch (\Throwable $e) {
            Log::error("CPay::{$context} exception", ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }
}
