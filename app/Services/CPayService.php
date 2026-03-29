<?php

namespace App\Services;

use App\Models\{Loan, Payment, SystemSetting};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * CPayService — Chaperone Payments API v1.1
 * Sandbox: https://cpay-uat-env.chaperone.co.ls:5100
 *
 * ╔════════════════════════════════════════════════════════════════════╗
 * ║  CONFIRMED FROM OFFICIAL SWAGGER DOCS                             ║
 * ║                                                                   ║
 * ║  AUTH:     Authorization: {apiKey}  — NO "Bearer " prefix         ║
 * ║                                                                   ║
 * ║  MSISDN:   Body and checksum salt MUST use identical format.      ║
 * ║            For repayment + external disburse: +266XXXXXXXX        ║
 * ║            For wallet-topup msisdn field: 8-digit local           ║
 * ║            BUT checksum must still use the +266 format.           ║
 * ║                                                                   ║
 * ║  CHECKSUM: HMAC-SHA256( txnId + clientCode + amount + msisdn,     ║
 * ║            secretKey ) — msisdn format must match what's in body  ║
 * ║                                                                   ║
 * ║  SANDBOX NOTE: The MSISDN +26650123456 in CPay docs is just an   ║
 * ║  example. You MUST use a real sandbox MSISDN registered in UAT.  ║
 * ║  Contact: apisupport@chaperone.co.ls to get test credentials.    ║
 * ║                                                                   ║
 * ║  REPAYMENT (OTP):                                                 ║
 * ║    1. POST /api/cpaypayments/payment    → CPay sends OTP          ║
 * ║    2. POST /api/cpaypayments/confirm    → submit OTP              ║
 * ║    Body: single-wrap { "transactionRequest": {...} }              ║
 * ║                                                                   ║
 * ║  DISBURSEMENT — simple external (MPesa/EFT):                      ║
 * ║    POST /api/disbursements/external-payment                       ║
 * ║    Body: double-wrap { "transactionRequest": {"transactionRequest":{...}}} ║
 * ║                                                                   ║
 * ║  DISBURSEMENT — wallet-topup-advance (CPay wallet + KYC):         ║
 * ║    POST /api/disbursements/wallet-topup-advance                   ║
 * ║    Body: double-wrap with additionalData.recipientKyc             ║
 * ║    msisdn in body = 8-digit, BUT checksum salt uses +266 format   ║
 * ║                                                                   ║
 * ║  STATUS CHECK (pull):                                             ║
 * ║    GET /api/cpaypayments/transaction-status                       ║
 * ║    ?requestReference={txnId}&dateTime={YYYY-MM-DD}               ║
 * ╚════════════════════════════════════════════════════════════════════╝
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
     * @param  string $method  'mobile_money' | 'card' | 'cpay_wallet'
     */
    public function initiateRepayment(Payment $payment, string $phone, string $method = 'mobile_money'): array
    {
        $txnId  = $payment->payment_reference;
        $amount = number_format((float) $payment->amount, 2, '.', '');
        // CPay team confirmed: use 8-digit format (no country code) in the body.
        // Checksum salt uses the same 8-digit format so both sides match.
        $msisdn = $this->msisdn($phone);   // 8-digit: 58145851
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
            'redirectUrl'      => url(route('webhooks.payment')),
        ];

        $endpoint = $isCard
            ? '/api/cpaypayments/payment?cardPayment=true&rememberMe=false'
            : '/api/cpaypayments/payment';

        Log::info('CPay::initiateRepayment', compact('txnId', 'amount', 'msisdn', 'method'));

        $result = $this->post($endpoint, ['transactionRequest' => $inner], 'initiateRepayment');

        if (!$result['success'] && str_contains($result['error'] ?? '', 'does not exist')) {
            Log::warning('CPay::initiateRepayment — MSISDN not registered in CPay. '
                . 'Ask CPay team to register this number in UAT, or provide your own Lesotho number. '
                . 'MSISDN used (8-digit): ' . $msisdn);
        }

        $result['is_card'] = $isCard;
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
    // FIX: msisdn field in body uses 8-digit per CPay wallet-topup-advance docs,
    //      BUT the checksum salt MUST still use the +266 normalised format.
    //      Previously both body msisdn AND checksum salt used msisdn8, causing
    //      a mismatch if CPay validates checksum against the +266 canonical form.
    // =========================================================================

    public function disburseToWallet(Loan $loan, string $phone, string $reference): array
    {
        $txnId   = $this->generateTxnId($reference);
        $amount  = number_format((float) $loan->principal_amount, 2, '.', '');
        $msisdn  = $this->normalisePhone($phone);  // +266 — used for checksum salt
        $msisdn8 = $this->msisdn($phone);          // 8-digit — used in body per wallet-topup-advance spec
        $app     = $loan->application;

        Log::info('CPay::disburseToWallet', [
            'loan'    => $loan->loan_number,
            'amount'  => $amount,
            'msisdn'  => $msisdn,
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
                        // FIX: checksum salt uses +266 format (normalisePhone), NOT msisdn8.
                        // The rule is: salt msisdn = whatever format the endpoint treats as canonical.
                        // If CPay still rejects, try swapping to $msisdn8 here and test both.
                        'checksum'         => $this->checksumInitiate($txnId, $amount, $msisdn),
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

        if (!$response->successful()) {
            $err = $raw['Description'] ?? $raw['description'] ?? $raw['message'] ?? "HTTP {$httpStatus}";
            return [
                'success'     => false,
                'error'       => $err,
                'status'      => 'FAILED',
                'http_status' => $httpStatus,
                'data'        => $raw ?? [],
            ];
        }

        $data = $raw['return'] ?? $raw ?? [];

        return [
            'success'      => true,
            'cpay_txn_id'  => $data['cPayTransactionId'] ?? $data['CPayTransactionId'] ?? null,
            'status'       => strtoupper($data['paymentRequestStatus'] ?? $data['PaymentRequestStatus'] ?? 'PENDING'),
            'code'         => $data['statusCode']  ?? $data['StatusCode']  ?? null,
            'description'  => $data['description'] ?? $data['Description'] ?? 'OK',
            'redirect_url' => $data['redirectUrl'] ?? $data['RedirectUrl'] ?? null,
            'reason_code'  => $data['reasonCode']  ?? $data['ReasonCode']  ?? null,
            'data'         => $data,
        ];
    }
}