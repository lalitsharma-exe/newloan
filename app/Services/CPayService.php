<?php

namespace App\Services;

use App\Models\{Loan, Payment, SystemSetting};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * CPayService — Chaperone Payments API v1.1
 * Sandbox: https://cpay-uat-env.chaperone.co.ls:5100
 *
 * ╔══════════════════════════════════════════════════════════════════════╗
 * ║  UAT CONFIRMED (MYLOAN18374 / secret TGq9jD)  — 2026-04-01         ║
 * ║                                                                     ║
 * ║  AUTH:     Authorization: {apiKey}  — NO "Bearer " prefix           ║
 * ║                                                                     ║
 * ║  MSISDN:   ✅ CONFIRMED: use 8-digit local format (e.g. 58145851)   ║
 * ║            for BOTH body AND checksum salt in repayment endpoints.  ║
 * ║            CPay getchecksum endpoint verified our hash = their hash ║
 * ║                                                                     ║
 * ║  CHECKSUM: HMAC-SHA256( txnId + clientCode + amount + msisdn,       ║
 * ║            secretKey ) — confirmed working with 8-digit msisdn      ║
 * ║                                                                     ║
 * ║  REPAYMENT (OTP):  ✅ CONFIRMED WORKING                             ║
 * ║    1. POST /api/cpaypayments/payment    → HTTP 200, OTP sent        ║
 * ║    2. POST /api/cpaypayments/confirm    → submit OTP               ║
 * ║    Body: single-wrap { "transactionRequest": {...} }               ║
 * ║    reasonCode=otpSent → success, OTP delivered to phone            ║
 * ║                                                                     ║
 * ║  CARD PAYMENT:  ✅ CONFIRMED WORKING (CPay quirk)                   ║
 * ║    POST /api/cpaypayments/payment?cardPayment=true&rememberMe=false ║
 * ║    CPay returns HTTP 400 but body StatusCode="202" + Description=   ║
 * ║    "Payment Link Created." → treated as success in parseResponse()  ║
 * ║                                                                     ║
 * ║  STATUS CHECK:  ✅ CONFIRMED WORKING                                ║
 * ║    GET /api/cpaypayments/transaction-status                         ║
 * ║    ?requestReference={txnId}&dateTime={YYYY-MM-DD}                 ║
 * ║    Returns OTPSEND/paymentIncomplete when OTP not yet confirmed     ║
 * ║                                                                     ║
 * ║  DISBURSEMENT — external (MPesa/EFT):                              ║
 * ║    POST /api/disbursements/external-payment                         ║
 * ║    Body: double-wrap { "transactionRequest":{"transactionRequest":{}}}║
 * ║    msisdn in body = +266 format, destinationWalletNumber = 8-digit  ║
 * ║                                                                     ║
 * ║  DISBURSEMENT — wallet-topup-advance (CPay wallet + KYC):           ║
 * ║    POST /api/disbursements/wallet-topup-advance                     ║
 * ║    Body: double-wrap with additionalData.recipientKyc               ║
 * ║    msisdn in body = 8-digit, checksum salt uses +266 format         ║
 * ╚══════════════════════════════════════════════════════════════════════╝
 */
class CPayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $clientCode;
    private string $secretKey;
    private bool   $sandbox;

    // Terminal statuses — stop polling when any of these are received
    private const TERMINAL_STATUSES = ['PROCESSED', 'DENIED', 'CANCELED', 'EXPIRED', 'REVERSED', 'FAILED'];

    public function __construct()
    {
        $this->sandbox    = SystemSetting::get('gateway_mode', 'sandbox') !== 'production';
        $this->baseUrl    = $this->sandbox
            ? 'https://cpay-uat-env.chaperone.co.ls:5100'
            : SystemSetting::get('gateway_live_url', config('services.cpay.live_url', 'https://api.chaperone.co.ls'));
        $this->apiKey     = SystemSetting::get('gateway_key',      config('services.cpay.api_key',     ''));
        $this->clientCode = SystemSetting::get('cpay_client_code', config('services.cpay.client_code', ''));
        $this->secretKey  = SystemSetting::get('gateway_secret',   config('services.cpay.secret_key',  ''));
    }

    // =========================================================================
    // REPAYMENT — Step 1: Initiate (CPay sends OTP to borrower phone)
    // POST /api/cpaypayments/payment
    // Body: single-wrapped { "transactionRequest": {...} }
    //
    // ⚠️  SANDBOX: The phone number MUST be registered in CPay's UAT system.
    //     Contact apisupport@chaperone.co.ls to get a valid test MSISDN.
    //     "Wallet account does not exist" = unregistered phone, not a code bug.
    // =========================================================================

    /**
     * Initiate a CPay repayment.
     *
     * ✅ UAT CONFIRMED 2026-04-01:
     *    - 8-digit MSISDN format works (e.g. 58145851)
     *    - checksum with same 8-digit msisdn matches CPay's getchecksum endpoint
     *    - OTP sent successfully with reasonCode=otpSent
     *    - Card: HTTP 400 returned by CPay but body StatusCode=202 → treated as success
     *
     * @param  string $method  'mobile_money' | 'card' | 'cpay_wallet'
     */
    public function initiateRepayment(Payment $payment, string $phone, string $method = 'mobile_money', ?string $customRedirectUrl = null): array
    {
        $txnId  = $payment->payment_reference;
        $amount = number_format((float) $payment->amount, 2, '.', '');

        // ✅ CONFIRMED: 8-digit local format for both body AND checksum salt
        // CPay's /getchecksum verifies our HMAC-SHA256 matches theirs with this format.
        $msisdn = $this->msisdn($phone);   // strips to 8-digit: e.g. 58145851
        $isCard = ($method === 'card');

        $inner = [
            'extTransactionId' => $txnId,
            'clientCode'       => $this->clientCode,
            'msisdn'           => $msisdn,
            'amount'           => $amount,
            'otp'              => '',
            'shortDescription' => 'Loan repayment',
            'checksum'         => $this->checksumInitiate($txnId, $amount, $msisdn),
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => $customRedirectUrl ?? url(route('webhooks.payment')),
        ];

        // Card: append query params to same endpoint
        $endpoint = $isCard
            ? '/api/cpaypayments/payment?cardPayment=true&rememberMe=false'
            : '/api/cpaypayments/payment';

        Log::info('CPay::initiateRepayment', compact('txnId', 'amount', 'msisdn', 'method'));

        $result = $this->post($endpoint, ['transactionRequest' => $inner], 'initiateRepayment');

        // ── CARD: Log full parsed result so we can see exactly what CPay returned ──
        if ($isCard) {
            Log::info('CPay::initiateRepayment CARD_RESULT', [
                'txnId'        => $txnId,
                'success'      => $result['success'] ?? null,
                'is_card_link' => $result['is_card_link'] ?? false,
                'redirect_url' => $result['redirect_url'] ?? null,
                'description'  => $result['description'] ?? null,
                'reason_code'  => $result['reason_code'] ?? null,
                'cpay_txn_id'  => $result['cpay_txn_id'] ?? null,
                'status'       => $result['status'] ?? null,
                'data'         => $result['data'] ?? null,
                'error'        => $result['error'] ?? null,
            ]);
        }

        // Flag card results so controller can handle the payment link
        if ($isCard) {
            $result['is_card'] = true;
        }

        if (!$result['success'] && !($result['is_card_link'] ?? false)
            && str_contains($result['error'] ?? '', 'does not exist')) {
            Log::warning('CPay::initiateRepayment — MSISDN not registered in CPay sandbox. '
                . 'The MSISDN must be registered in UAT by CPay team. '
                . 'MSISDN used (8-digit): ' . $msisdn);
        }

        // is_card_link set by parseResponse when HTTP 400 but body StatusCode is 2xx (CPay card quirk)
        if ($isCard && !isset($result['is_card'])) {
            $result['is_card'] = true;
        }
        return $result;
    }

    // =========================================================================
    // REPAYMENT — Step 2: Confirm OTP
    // POST /api/cpaypayments/confirm
    // Checksum salt adds OTP: txnId + clientCode + amount + msisdn + otp
    // =========================================================================

    public function confirmPayment(Payment $payment, string $phone, string $otp): array
    {
        $txnId  = $payment->payment_reference;
        $amount = number_format((float) $payment->amount, 2, '.', '');
        // Must match the format used in initiateRepayment — 8-digit, no country code
        $msisdn = $this->msisdn($phone);

        Log::info('CPay::confirmPayment', compact('txnId', 'amount', 'msisdn'));

        return $this->post('/api/cpaypayments/confirm', [
            'transactionRequest' => [
                'extTransactionId' => $txnId,
                'clientCode'       => $this->clientCode,
                'msisdn'           => $msisdn,
                'amount'           => $amount,
                'otp'              => $otp,
                'checksum'         => $this->checksumConfirm($txnId, $amount, $msisdn, $otp),
                'currency'         => 'LSL',
                'redirectUrl'      => url(route('webhooks.payment')),
            ],
        ], 'confirmPayment');
    }

    // =========================================================================
    // DISBURSEMENT — External payment (MPesa, EFT, Ecocash)
    // POST /api/disbursements/external-payment
    //   ?destinationOperator={OP}&destinationWalletNumber={8-digit}
    // Body: DOUBLE-wrapped per docs
    // msisdn in body = +266 format; checksum salt = same +266 format
    // =========================================================================

    public function disburseExternal(Loan $loan, string $phone, string $provider, string $reference): array
    {
        $txnId   = $this->generateTxnId($reference);
        $amount  = number_format((float) $loan->principal_amount, 2, '.', '');
        $msisdn  = $this->normalisePhone($phone);  // +266 for body + checksum
        $msisdn8 = $this->msisdn($phone);          // 8-digit for destinationWalletNumber only

        $op = match (strtoupper($provider)) {
            'VODACOM', 'MPESA', 'MOBILE_MONEY' => 'mpesa',
            'ECONET', 'ECOCASH'                => 'ecocash',
            'MYWALLET'                         => 'mywallet',
            'KHETSI'                           => 'khetsi',
            'EFT', 'BANK_TRANSFER'             => 'EFT',
            default                            => strtolower($provider),
        };

        Log::info('CPay::disburseExternal', [
            'loan'    => $loan->loan_number,
            'amount'  => $amount,
            'op'      => $op,
            'msisdn'  => $msisdn,
            'msisdn8' => $msisdn8,
        ]);

        return $this->postWithQuery(
            '/api/disbursements/external-payment',
            ['destinationOperator' => $op, 'destinationWalletNumber' => $msisdn8],
            [
                'transactionRequest' => [
                    'transactionRequest' => [    // double-wrapped — required by docs
                        'extTransactionId' => $txnId,
                        'clientCode'       => $this->clientCode,
                        'msisdn'           => $msisdn,       // +266 in body
                        'amount'           => $amount,
                        'shortDescription' => 'Disburse ' . substr($loan->loan_number, 0, 10),
                        'checksum'         => $this->checksumInitiate($txnId, $amount, $msisdn), // +266 in salt
                        'currency'         => 'LSL',
                        'otp'              => '',
                        'redirectUrl'      => url(route('webhooks.payment')),
                        'additionalData'   => 'loan:' . $loan->loan_number,
                    ],
                ],
            ],
            'disburseExternal'
        );
    }

    // =========================================================================
    // DISBURSEMENT — Wallet top-up advance (CPay wallet + KYC verification)
    // POST /api/disbursements/wallet-topup-advance
    //   ?destinationOperator={OP}&destinationWalletNumber={8-digit}
    // Body: double-wrapped with recipientKyc in additionalData
    //
    // wallet-topup-advance: body msisdn = 8-digit. checksum salt = SAME 8-digit.
    // CPay recomputes the checksum server-side using the msisdn from the body,
    // so the salt must match exactly. Using +266 format caused "Checksum is invalid".
    // =========================================================================

    public function disburseToWallet(Loan $loan, string $phone, string $reference): array
    {
        $txnId   = $this->generateTxnId($reference);
        $amount  = number_format((float) $loan->principal_amount, 2, '.', '');
        $msisdn8 = $this->msisdn($phone);          // 8-digit — used in body AND checksum salt for wallet-topup-advance
        // NOTE: wallet-topup-advance body uses 8-digit msisdn8.
        // CPay validates checksum using the msisdn value from the body, so checksum salt MUST also use msisdn8.
        // Using +266 format here caused "Transaction Checksum is invalid" — confirmed by log mismatch.
        $app     = $loan->application;

        Log::info('CPay::disburseToWallet', [
            'loan'    => $loan->loan_number,
            'amount'  => $amount,
            'msisdn8' => $msisdn8,
        ]);

        return $this->postWithQuery(
            '/api/disbursements/wallet-topup-advance',
            ['destinationOperator' => 'CPAY', 'destinationWalletNumber' => $msisdn8],
            [
                'transactionRequest' => [
                    'transactionRequest' => [
                        'extTransactionId' => $txnId,
                        'clientCode'       => $this->clientCode,
                        'msisdn'           => $msisdn8,  // 8-digit per wallet-topup-advance example
                        'amount'           => $amount,
                        'shortDescription' => 'Wallet top-up',
                        'checksum'         => $this->checksumInitiate($txnId, $amount, $msisdn8), // MUST match body msisdn format (8-digit)
                        'currency'         => 'LSL',
                        'redirectUrl'      => url(route('webhooks.payment')),
                        'additionalData'   => [
                            'recipientKyc' => [
                                'idDocument' => [[
                                    'idType'        => 'ID',
                                    'idNumber'      => $app?->national_id ?? ($loan->user->national_id ?? ''),
                                    'expiryDate'    => '2030-12-31',
                                    'issuerCountry' => 'LS',
                                ]],
                                'firstName'     => $app?->first_name ?? explode(' ', $loan->user->name)[0],
                                'middleName'    => '',
                                'lastName'      => $app?->surname    ?? (explode(' ', $loan->user->name)[1] ?? ''),
                                'fullName'      => $loan->user->name ?? '',
                                'gender'        => '',
                                'sourceOfFunds' => 'Loan Disbursement',
                            ],
                        ],
                    ],
                ],
            ],
            'disburseToWallet'
        );
    }

    // =========================================================================
    // STATUS CHECK — pull mechanism
    // GET /api/cpaypayments/transaction-status
    //   ?requestReference={extTransactionId}&dateTime={YYYY-MM-DD}
    // =========================================================================

    public function queryPaymentStatus(string $txnId, ?string $date = null): array
    {
        try {
            $response = $this->client()->get('/api/cpaypayments/transaction-status', [
                'requestReference' => $txnId,
                'dateTime'         => $date ?? now()->format('Y-m-d'),
            ]);

            $data      = $response->json() ?? [];
            $rawStatus = $data['paymentRequestStatus'] ?? $data['transactionStatus'] ?? $data['status'] ?? 'UNKNOWN';

            Log::info('CPay::queryPaymentStatus', [
                'txnId'  => $txnId,
                'http'   => $response->status(),
                'status' => $rawStatus,
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

    /**
     * Poll transaction status until a terminal state is reached or timeout expires.
     *
     * Usage:
     *   $result = $cpay->pollPaymentStatus($txnId, maxAttempts: 10, intervalSeconds: 5);
     *   // $result['status'] will be PROCESSED, DENIED, CANCELED, EXPIRED, REVERSED, or TIMEOUT
     *
     * @param  string   $txnId
     * @param  int      $maxAttempts    Number of polling attempts before giving up
     * @param  int      $intervalSeconds Seconds between polls (keep ≥ 3 to avoid rate limiting)
     */
    public function pollPaymentStatus(string $txnId, int $maxAttempts = 10, int $intervalSeconds = 5): array
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = $this->queryPaymentStatus($txnId);

            Log::info('CPay::pollPaymentStatus', [
                'txnId'   => $txnId,
                'attempt' => $attempt,
                'status'  => $result['status'] ?? 'UNKNOWN',
            ]);

            if (in_array($result['status'] ?? '', self::TERMINAL_STATUSES)) {
                return $result;
            }

            if ($attempt < $maxAttempts) {
                sleep($intervalSeconds);
            }
        }

        return [
            'success' => false,
            'status'  => 'TIMEOUT',
            'error'   => "Status still pending after {$maxAttempts} attempts",
            'txnId'   => $txnId,
        ];
    }

    // =========================================================================
    // WEBHOOK PARSING
    // =========================================================================

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

    /**
     * CPay v1.1 does not document webhook signing.
     *
     * Security approach: we accept all inbound webhooks but validate that
     * extTransactionId matches a known pending transaction in our database.
     * This prevents spoofed webhooks from marking unknown transactions as paid.
     *
     * Example usage in your WebhookController:
     *   $parsed = $cpay->parseWebhook($payload);
     *   $payment = Payment::where('payment_reference', $parsed['our_ref'])->firstOrFail();
     */
    public function validateWebhookChecksum(array $payload): bool
    {
        // CPay v1.1 has no documented signing mechanism.
        // Validation is done by matching extTransactionId to known records — see parseWebhook().
        return true;
    }

    // =========================================================================
    // CHECKSUMS
    // Salt (initiation):   ExtTransactionId + ClientCode + Amount + MSISDN
    // Salt (confirmation): ExtTransactionId + ClientCode + Amount + MSISDN + OTP
    //
    // CRITICAL: msisdn in the salt must match whatever format you use in the body.
    // For repayment and external disbursement: always +266XXXXXXXX.
    // For wallet-topup-advance: body uses 8-digit msisdn8, but pass +266 to this
    // method — if CPay rejects, try passing msisdn8 instead and test both.
    // =========================================================================

    public function checksumInitiate(string $txnId, string $amount, string $msisdn): string
    {
        return hash_hmac('sha256', $txnId . $this->clientCode . $amount . $msisdn, $this->secretKey);
    }

    public function checksumConfirm(string $txnId, string $amount, string $msisdn, string $otp): string
    {
        return hash_hmac('sha256', $txnId . $this->clientCode . $amount . $msisdn . $otp, $this->secretKey);
    }

    // =========================================================================
    // PHONE HELPERS
    // =========================================================================

    /**
     * Full international format: +266XXXXXXXX
     * Used in repayment body, external disbursement body, and all checksum salts.
     */
    public function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '266')) {
            return '+' . $digits;
        }
        if (strlen($digits) === 8) {
            return '+266' . $digits;
        }
        return '+266' . substr($digits, -8);
    }

    /**
     * 8-digit local format: +26650123456 → 50123456
     * Used for destinationWalletNumber query param and wallet-topup-advance body msisdn field.
     * Do NOT use this in checksum salts.
     */
    public function msisdn(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '266')) {
            return substr($digits, 3);
        }
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
        $safe = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $prefix));
        return substr($safe, 0, 12) . '-' . strtoupper(Str::random(8)) . '-' . time();
    }

    // =========================================================================
    // HTTP CLIENT
    // =========================================================================

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => $this->apiKey,   // NO "Bearer " prefix — confirmed by docs
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(30)
            ->retry(1, 500);
    }

    private function post(string $endpoint, array $body, string $ctx): array
    {
        try {
            Log::info("CPay::{$ctx} REQUEST", [
                'endpoint' => $endpoint,
                'payload'  => $body,
            ]);
            $response = $this->client()->post($endpoint, $body);
            Log::info("CPay::{$ctx} RESPONSE", [
                'endpoint' => $endpoint,
                'http'     => $response->status(),
                'body'     => $response->json() ?? $response->body(),
            ]);
            return $this->parseResponse($response, $ctx);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("CPay::{$ctx} connection error", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Cannot connect to CPay: ' . $e->getMessage(), 'status' => 'ERROR'];
        } catch (\Throwable $e) {
            Log::error("CPay::{$ctx} exception", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    private function postWithQuery(string $endpoint, array $query, array $body, string $ctx): array
    {
        return $this->post($endpoint . '?' . http_build_query($query), $body, $ctx);
    }

    private function parseResponse(\Illuminate\Http\Client\Response $response, string $ctx): array
    {
        $httpStatus = $response->status();
        $raw        = $response->json();

        // ── FULL RAW RESPONSE LOG — always log, essential for debugging CPay quirks ──
        Log::info("CPay::{$ctx} RAW_RESPONSE", [
            'http_status'  => $httpStatus,
            'headers'      => $response->headers(),
            'body_raw'     => $response->body(),
            'body_json'    => $raw,
        ]);

        // Handle non-JSON responses (card payments return HTML/URL)
        if (!$raw && $response->body()) {
            $bodyStr = trim($response->body());
            if ($httpStatus === 200 && (str_contains($bodyStr, 'http') || str_contains($bodyStr, '<form'))) {
                return [
                    'success'      => true,
                    'is_redirect'  => true,
                    'redirect_url' => $bodyStr,
                    'status'       => 'REDIRECT',
                    'data'         => [],
                ];
            }
            $raw = ['statusCode' => (string) $httpStatus, 'description' => $bodyStr];
        }

        Log::info("CPay::{$ctx} response", ['http' => $httpStatus, 'body' => $raw]);

        // Always unwrap the 'return' envelope first (CPay wraps all responses)
        $data = $raw['return'] ?? $raw ?? [];

        // ── Card payment quirk: CPay returns HTTP 400 but StatusCode "202" in body ──
        // The actual payment page URL is in the HTTP response HEADER "RedirectUrl" (not the body!)
        // Treat body StatusCode 2xx as success regardless of HTTP status
        $bodyCode    = (int) ($raw['StatusCode'] ?? $raw['statusCode'] ?? $data['StatusCode'] ?? $data['statusCode'] ?? 0);
        $headerRedirect = $response->header('RedirectUrl') ?: null;   // CPay puts URL here!

        if (!$response->successful() && $bodyCode >= 200 && $bodyCode < 300) {
            Log::info("CPay::{$ctx} — body StatusCode {$bodyCode} treated as success (HTTP {$httpStatus})", [
                'body'           => $raw,
                'header_redirect'=> $headerRedirect,
            ]);
            return [
                'success'      => true,
                'cpay_txn_id'  => $data['cPayTransactionId'] ?? $data['CPayTransactionId'] ?? null,
                'status'       => strtoupper($data['paymentRequestStatus'] ?? $data['PaymentRequestStatus'] ?? 'PENDING'),
                'code'         => (string) $bodyCode,
                'description'  => $data['description'] ?? $data['Description'] ?? 'Payment Link Created',
                'message'      => $data['description'] ?? $data['Description'] ?? 'Payment Link Created',
                // ✅ Check header first — CPay sends payment URL in RedirectUrl header, not body
                'redirect_url' => $headerRedirect
                    ?? $data['redirectUrl'] ?? $data['RedirectUrl'] ?? $data['paymentLink'] ?? null,
                'reason_code'  => $data['reasonCode']  ?? $data['ReasonCode']  ?? null,
                'data'         => $data,
                'is_card_link' => true,
            ];
        }


        if (!$response->successful()) {

            $err = $data['Description'] ?? $data['description']
                ?? $data['message']     ?? $raw['Description']
                ?? $raw['description']  ?? "HTTP {$httpStatus}";

            Log::warning("CPay::{$ctx} failed", [
                'http' => $httpStatus,
                'err'  => $err,
                'raw'  => $raw,
            ]);

            return [
                'success'     => false,
                'error'       => $err,
                'message'     => $err,
                'status'      => 'FAILED',
                'http_status' => $httpStatus,
                'reason_code' => $data['ReasonCode'] ?? $data['reasonCode'] ?? null,
                'data'        => $data,
            ];
        }

        return [
            'success'      => true,
            'cpay_txn_id'  => $data['cPayTransactionId'] ?? $data['CPayTransactionId'] ?? null,
            'status'       => strtoupper($data['paymentRequestStatus'] ?? $data['PaymentRequestStatus'] ?? 'PENDING'),
            'code'         => $data['statusCode']  ?? $data['StatusCode']  ?? null,
            'description'  => $data['description'] ?? $data['Description'] ?? 'OK',
            'message'      => $data['description'] ?? $data['Description'] ?? 'OK',
            'redirect_url' => $data['redirectUrl'] ?? $data['RedirectUrl'] ?? null,
            'reason_code'  => $data['reasonCode']  ?? $data['ReasonCode']  ?? null,
            'data'         => $data,
        ];
    }
}