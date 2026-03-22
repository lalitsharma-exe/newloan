<?php

namespace App\Services;

use App\Models\{Loan, Payment, SystemSetting};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * CPayService — Chaperone Payments API
 * Sandbox: https://cpay-uat-env.chaperone.co.ls:5100
 *
 * FLOW:
 *   1. POST /api/cpaypayments/payment   → CPay sends OTP to user's phone
 *   2. POST /api/cpaypayments/confirm   → Submit OTP to confirm payment
 *
 * AUTH:
 *   Authorization: <API Key>   (NO "Bearer " prefix!)
 *
 * CHECKSUM (initiation):
 *   salt   = ExtTrasactionId + ClientCode + Amount + msisdn
 *   hash   = HMAC-SHA256(salt, secretKey) — hex string
 *
 * CHECKSUM (confirmation):
 *   salt   = ExtTrasactionId + ClientCode + Amount + msisdn + OTP
 *   hash   = HMAC-SHA256(salt, secretKey) — hex string
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
            : SystemSetting::get('gateway_live_url', config('services.cpay.live_url', 'https://api.chaperone.co.ls'));
        $this->apiKey     = SystemSetting::get('gateway_key',     config('services.cpay.api_key',     ''));
        $this->clientCode = SystemSetting::get('cpay_client_code', config('services.cpay.client_code', ''));
        $this->secretKey  = SystemSetting::get('gateway_secret',  config('services.cpay.secret_key',  ''));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 1 — Initiate payment (CPay sends OTP to user)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Initiate a repayment via CPay.
     *
     * CPay will send an OTP to the user's msisdn.
     * For card payments (method='card'), CPay returns an HTML redirect URL.
     * For mobile/wallet, CPay sends an OTP — caller must then call confirmPayment().
     *
     * @param  Payment $payment
     * @param  string  $phone    Borrower phone (any format, normalised internally)
     * @param  string  $method   'mobile_money' | 'card' | 'cpay_wallet'
     * @return array   ['success', 'is_card', 'redirect_url', 'message', 'data', 'error']
     */
    public function initiateRepayment(Payment $payment, string $phone, string $method = 'mobile_money'): array
    {
        $txnId    = $payment->payment_reference;
        $amount   = number_format((float) $payment->amount, 2, '.', '');
        $msisdn   = $this->normalisePhone($phone);
        $isCard   = ($method === 'card');

        $checksum = $this->checksumInitiate($txnId, $amount, $msisdn);

        $inner = [
            'extTransactionId' => $txnId,
            'clientCode'       => $this->clientCode,
            'msisdn'           => $msisdn,
            'amount'           => $amount,
            'shortDescription' => 'Loan repayment',   // max 20 chars
            'checksum'         => $checksum,
            'currency'         => 'LSL',
            'redirectUrl'      => url(route('borrower.payments.callback.success', ['ref' => $txnId])),
        ];

        // Card payments don't need otpMedium; mobile/wallet do
        if (!$isCard) {
            $inner['otpMedium'] = 'sms';
        }

        $body = ['transactionRequest' => $inner];

        // Card payments add ?cardPayment=true query param
        $endpoint = $isCard
            ? '/api/cpaypayments/payment?cardPayment=true'
            : '/api/cpaypayments/payment';

        Log::info('CPay::initiateRepayment', [
            'txnId'    => $txnId,
            'amount'   => $amount,
            'msisdn'   => $msisdn,
            'method'   => $method,
            'isCard'   => $isCard,
        ]);

        $result = $this->post($endpoint, $body, 'initiateRepayment');
        $result['is_card'] = $isCard;
        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STEP 2 — Confirm payment with OTP
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Confirm a CPay payment using the OTP entered by the user.
     *
     * @param  Payment $payment
     * @param  string  $phone    Same phone used in initiateRepayment
     * @param  string  $otp      OTP received by user via SMS
     * @return array   ['success', 'message', 'data', 'error']
     */
    public function confirmPayment(Payment $payment, string $phone, string $otp): array
    {
        $txnId  = $payment->payment_reference;
        $amount = number_format((float) $payment->amount, 2, '.', '');
        $msisdn = $this->normalisePhone($phone);

        $checksum = $this->checksumConfirm($txnId, $amount, $msisdn, $otp);

        // Wrapped camelCase format (consistent with initiate)
        $body = [
            'transactionRequest' => [
                'extTransactionId' => $txnId,
                'clientCode'       => $this->clientCode,
                'msisdn'           => $msisdn,
                'currency'         => 'LSL',
                'amount'           => $amount,
                'otp'              => $otp,
                'checksum'         => $checksum,
                'redirectUrl'      => url(route('borrower.payments.callback.success', ['ref' => $txnId])),
            ],
        ];

        Log::info('CPay::confirmPayment', [
            'txnId'    => $txnId,
            'amount'   => $amount,
            'msisdn'   => $msisdn,
        ]);

        return $this->post('/api/cpaypayments/confirm', $body, 'confirmPayment');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DISBURSEMENTS — send loan funds to borrower
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Disburse loan funds to borrower via external mobile money / EFT.
     * Endpoint: POST /api/disbursements/external-payment
     */
    public function disburseExternal(Loan $loan, string $phone, string $provider, string $reference): array
    {
        $txnId  = $this->generateTxnId($reference);
        $amount = number_format((float) $loan->principal_amount, 2, '.', '');
        $msisdn = $this->normalisePhone($phone);

        $body = [
            'transactionRequest' => [
                'transactionRequest' => [
                    'extTransactionId' => $txnId,
                    'clientCode'       => $this->clientCode,
                    'msisdn'           => $msisdn,
                    'amount'           => $amount,
                    'shortDescription' => 'Loan disbursement ' . $loan->loan_number,
                    'checksum'         => $this->checksumInitiate($txnId, $amount, $msisdn),
                    'currency'         => 'LSL',
                    'redirectUrl'      => url(route('webhooks.payment')),
                    'additionalData'   => 'loan:' . $loan->loan_number,
                ],
            ],
        ];

        $queryParams = [
            'destinationOperator'     => strtoupper($provider),
            'destinationWalletNumber' => $this->msisdn($phone),
        ];

        return $this->postWithQuery('/api/disbursements/external-payment', $queryParams, $body, 'disburseExternal');
    }

    /**
     * Query payment status.
     * Endpoint: GET /api/disbursements/payment-inquiry
     */
    public function queryPaymentStatus(string $txnId): array
    {
        try {
            $response = $this->client()->get('/api/disbursements/payment-inquiry', [
                'transactionId' => $txnId,
            ]);

            $data      = $response->json() ?? [];
            $rawStatus = $data['paymentRequestStatus'] ?? $data['transactionStatus'] ?? $data['status'] ?? 'UNKNOWN';

            Log::info('CPay::queryPaymentStatus', [
                'txnId'  => $txnId,
                'status' => $response->status(),
                'body'   => $data,
            ]);

            return [
                'success'     => $response->successful(),
                'status'      => strtoupper($rawStatus),
                'cpay_txn_id' => $data['cPayTransactionId'] ?? null,
                'data'        => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('CPay::queryPaymentStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'ERROR', 'error' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WEBHOOK
    // ─────────────────────────────────────────────────────────────────────────

    public function parseWebhook(array $payload): array
    {
        $data   = $payload['return'] ?? $payload;
        $status = strtoupper($data['paymentRequestStatus'] ?? $data['transactionStatus'] ?? $data['status'] ?? '');

        return [
            'cpay_txn_id' => $data['cPayTransactionId'] ?? null,
            'our_ref'     => $data['extTransactionId']  ?? $data['transactionId'] ?? null,
            'amount'      => (float) ($data['amount'] ?? 0),
            'status'      => $status,
            'is_success'  => $status === 'PROCESSED',
            'is_failed'   => in_array($status, ['DENIED', 'CANCELED', 'EXPIRED']),
            'is_reversed' => $status === 'REVERSED',
            'raw'         => $payload,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHECKSUMS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Checksum for payment INITIATION.
     * Salt = ExtTrasactionId + ClientCode + Amount + msisdn
     */
    public function checksumInitiate(string $txnId, string $amount, string $msisdn): string
    {
        $salt = $txnId . $this->clientCode . $amount . $msisdn;
        return hash_hmac('sha256', $salt, $this->secretKey);
    }

    /**
     * Checksum for payment CONFIRMATION (includes OTP).
     * Salt = ExtTrasactionId + ClientCode + Amount + msisdn + OTP
     */
    public function checksumConfirm(string $txnId, string $amount, string $msisdn, string $otp): string
    {
        $salt = $txnId . $this->clientCode . $amount . $msisdn . $otp;
        return hash_hmac('sha256', $salt, $this->secretKey);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Full international format +266XXXXXXXX
     */
    public function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 8) return '+266' . $digits;
        if (str_starts_with($digits, '266') && strlen($digits) >= 11) return '+' . substr($digits, 0, 11);
        if (str_starts_with($phone, '+266')) return substr($phone, 0, 13); // +266 + 8 digits
        return '+266' . substr($digits, -8);
    }

    /**
     * 8-digit local format (no country code)
     */
    public function msisdn(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '266')) return substr($digits, 3);
        if (strlen($digits) === 12 && str_starts_with($digits, '266')) return substr($digits, 3);
        return substr($digits, -8);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->clientCode) && !empty($this->secretKey);
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    private function generateTxnId(string $prefix = ''): string
    {
        $safe = preg_replace('/[^A-Za-z0-9]/', '', $prefix);
        return strtoupper(substr($safe, 0, 10)) . '-' . strtoupper(Str::random(8)) . '-' . time();
    }

    /**
     * HTTP client — Authorization: <API Key>  (NO "Bearer" prefix — per CPay docs)
     */
    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => $this->apiKey,   // <-- NO "Bearer " prefix!
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(20)
            ->retry(1, 0);
    }

    private function post(string $endpoint, array $body, string $context): array
    {
        try {
            $response = $this->client()->post($endpoint, $body);

            Log::info("CPay::{$context} response", [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->json() ?? $response->body(),
                'sandbox'  => $this->sandbox,
            ]);

            return $this->parseResponse($response, $context);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("CPay::{$context} connection error", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Cannot connect to CPay: ' . $e->getMessage(), 'status' => 'ERROR'];
        } catch (\Throwable $e) {
            Log::error("CPay::{$context} exception", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    private function postWithQuery(string $endpoint, array $query, array $body, string $context): array
    {
        $url = $endpoint . '?' . http_build_query($query);
        return $this->post($url, $body, $context);
    }

    private function parseResponse(\Illuminate\Http\Client\Response $response, string $context): array
    {
        if ($response->successful()) {
            $data      = $response->json()['return'] ?? $response->json() ?? [];
            $rawStatus = $data['paymentRequestStatus'] ?? $data['transactionStatus'] ?? $data['status'] ?? 'PENDING';
            $status    = strtoupper($rawStatus);

            return [
                'success'      => true,
                'cpay_txn_id'  => $data['cPayTransactionId'] ?? null,
                'status'       => $status,
                'redirect_url' => $data['redirectUrl'] ?? $data['paymentUrl'] ?? null,
                'message'      => $data['description'] ?? $data['message'] ?? $data['Description'] ?? 'OK',
                'data'         => $data,
            ];
        }

        $err = $response->json('description')
            ?? $response->json('Description')
            ?? $response->json('message')
            ?? $response->json('error')
            ?? "HTTP {$response->status()}";

        Log::warning("CPay::{$context} failed", [
            'http_status' => $response->status(),
            'error'       => $err,
            'body'        => $response->body(),
        ]);

        return [
            'success'     => false,
            'error'       => $err,
            'status'      => 'FAILED',
            'http_status' => $response->status(),
        ];
    }
}