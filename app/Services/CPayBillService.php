<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * CPayBillService — Chaperone Payments API for Bill Payments
 *
 * ╔════════════════════════════════════════════════════════════════════════╗
 * ║  ISOLATED from CPayService.php — shares same credentials but has    ║
 * ║  completely separate methods for airtime, electricity, insurance,    ║
 * ║  and event ticket purchases.                                        ║
 * ║                                                                     ║
 * ║  DO NOT modify CPayService.php. This class is self-contained.       ║
 * ╚════════════════════════════════════════════════════════════════════════╝
 *
 * Endpoints used:
 *   Airtime:     POST /api/paybills/airtime/purchase?type={VCL|ETL}
 *                GET  /api/paybills/airtime/list
 *   Electricity: POST /api/paybills/electricity/purchase?meterNumber={X}
 *                GET  /api/paybills/electricity/customerlookup?meternumber={X}
 *                GET  /api/paybills/electricity/tokens?meterNumber={X}&clientCode={C}
 *   Insurance:   POST /api/paybills/insurance/payment?partnerId={P}&policyNumber={N}
 *                GET  /api/paybills/insurance/member?policyNumber={N}&partnerId={P}
 *                GET  /api/paybills/insurance/list
 *   Tickets:     POST /api/chaperone-vas/tickets/purchase
 *                GET  /api/chaperone-vas/tickets/list
 *                GET  /api/chaperone-vas/tickets/details?eventId={E}&type={T}
 *   Status:      GET  /api/cpaypayments/transaction-status
 */
class CPayBillService
{
    private string $baseUrl;
    private string $apiKey;
    private string $clientCode;
    private string $secretKey;
    private bool   $sandbox;

    public function __construct()
    {
        $this->sandbox = SystemSetting::get('gateway_mode', 'sandbox') !== 'production';
        $rawUrl = $this->sandbox
            ? 'https://cpay-uat-env.chaperone.co.ls:5100'
            : SystemSetting::get('gateway_live_url', config('services.cpay.live_url', 'https://api.chaperone.co.ls'));

        $this->baseUrl    = rtrim(rtrim($rawUrl, '/'), '/api');
        $this->apiKey     = SystemSetting::get('gateway_key',      config('services.cpay.api_key',     ''));
        $this->clientCode = SystemSetting::get('cpay_client_code', config('services.cpay.client_code', ''));
        $this->secretKey  = SystemSetting::get('gateway_secret',   config('services.cpay.secret_key',  ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->clientCode) && !empty($this->secretKey);
    }

    // =========================================================================
    //  AIRTIME
    // =========================================================================

    /**
     * List available airtime denominations.
     */
    public function listAirtime(): array
    {
        return $this->get('/api/paybills/airtime/list', [], 'listAirtime');
    }

    /**
     * Purchase airtime for a phone number.
     *
     * @param string $phone    Recipient phone (8-digit or +266 format)
     * @param float  $amount   Airtime amount in LSL
     * @param string $type     MNO type: 'VCL' (Vodacom) or 'ETL' (Econet)
     * @param string $txnId    External transaction ID
     */
    public function purchaseAirtime(string $phone, float $amount, string $type, string $txnId): array
    {
        $msisdn = $this->msisdn($phone);
        $amt    = number_format($amount, 2, '.', '');

        Log::info('CPayBill::purchaseAirtime', compact('txnId', 'msisdn', 'amt', 'type'));

        return $this->post(
            "/api/paybills/airtime/purchase?type={$type}",
            ['transactionRequest' => $this->buildRequest($txnId, $msisdn, $amt, "Airtime {$type}")],
            'purchaseAirtime'
        );
    }

    // =========================================================================
    //  ELECTRICITY
    // =========================================================================

    /**
     * Lookup a meter number to validate it exists.
     */
    public function lookupMeter(string $meterNumber): array
    {
        return $this->get(
            '/api/paybills/electricity/customerlookup',
            ['meternumber' => $meterNumber],
            'lookupMeter'
        );
    }

    /**
     * Purchase a prepaid electricity token.
     *
     * @param string $meterNumber  Customer's electricity meter number
     * @param float  $amount       Token amount in LSL
     * @param string $phone        Client phone for checksum
     * @param string $txnId        External transaction ID
     */
    public function purchaseElectricity(string $meterNumber, float $amount, string $phone, string $txnId): array
    {
        $msisdn = $this->msisdn($phone);
        $amt    = number_format($amount, 2, '.', '');

        Log::info('CPayBill::purchaseElectricity', compact('txnId', 'meterNumber', 'msisdn', 'amt'));

        return $this->post(
            "/api/paybills/electricity/purchase?meterNumber={$meterNumber}",
            ['transactionRequest' => $this->buildRequest($txnId, $msisdn, $amt, "Electricity token")],
            'purchaseElectricity'
        );
    }

    /**
     * Get last purchased electricity tokens for a meter.
     */
    public function getElectricityTokens(string $meterNumber): array
    {
        return $this->get(
            '/api/paybills/electricity/tokens',
            ['meterNumber' => $meterNumber, 'clientCode' => $this->clientCode],
            'getElectricityTokens'
        );
    }

    // =========================================================================
    //  INSURANCE
    // =========================================================================

    /**
     * List available insurance providers.
     */
    public function listInsuranceProviders(): array
    {
        return $this->get('/api/paybills/insurance/list', [], 'listInsuranceProviders');
    }

    /**
     * Lookup an insurance member by policy number.
     */
    public function lookupInsuranceMember(string $policyNumber, int $partnerId): array
    {
        return $this->get(
            '/api/paybills/insurance/member',
            ['policyNumber' => $policyNumber, 'partnerId' => $partnerId],
            'lookupInsuranceMember'
        );
    }

    /**
     * Pay an insurance premium.
     *
     * @param string $policyNumber  Member's policy number
     * @param int    $partnerId     Insurance provider ID from list
     * @param float  $amount        Payment amount
     * @param string $phone         Client phone for checksum
     * @param string $txnId         External transaction ID
     */
    public function payInsurance(string $policyNumber, int $partnerId, float $amount, string $phone, string $txnId): array
    {
        $msisdn = $this->msisdn($phone);
        $amt    = number_format($amount, 2, '.', '');

        Log::info('CPayBill::payInsurance', compact('txnId', 'policyNumber', 'partnerId', 'msisdn', 'amt'));

        return $this->post(
            "/api/paybills/insurance/payment?partnerId={$partnerId}&policyNumber={$policyNumber}",
            ['transactionRequest' => $this->buildRequest($txnId, $msisdn, $amt, "Insurance premium")],
            'payInsurance'
        );
    }

    // =========================================================================
    //  EVENT TICKETS
    // =========================================================================

    /**
     * List available events/vouchers.
     */
    public function listEvents(): array
    {
        return $this->get('/api/chaperone-vas/tickets/list', [], 'listEvents');
    }

    /**
     * Get event/voucher details by ID.
     */
    public function getEventDetails(string $eventId, string $type = 'events'): array
    {
        return $this->get(
            '/api/chaperone-vas/tickets/details',
            ['eventId' => $eventId, 'type' => $type],
            'getEventDetails'
        );
    }

    /**
     * Purchase an event ticket.
     *
     * @param array  $ticketInfo  { eventType, eventId, ticketId, quantity, ticketName, beneficiaryName }
     * @param float  $amount      Ticket price
     * @param string $phone       Client phone
     * @param string $txnId       External transaction ID
     */
    public function purchaseTicket(array $ticketInfo, float $amount, string $phone, string $txnId): array
    {
        $msisdn = $this->msisdn($phone);
        $amt    = number_format($amount, 2, '.', '');

        Log::info('CPayBill::purchaseTicket', compact('txnId', 'msisdn', 'amt', 'ticketInfo'));

        // Tickets use the double-wrap pattern like disbursements
        return $this->post(
            '/api/chaperone-vas/tickets/purchase',
            [
                'transactionRequest' => [
                    'transactionRequest' => [
                        'extTransactionId' => $txnId,
                        'clientCode'       => $this->clientCode,
                        'msisdn'           => $msisdn,
                        'amount'           => $amt,
                        'shortDescription' => 'Ticket purchase',
                        'checksum'         => $this->checksum($txnId, $amt, $msisdn),
                        'redirectUrl'      => url('/'),
                        'additionalData'   => [
                            'ticketInformation' => $ticketInfo,
                        ],
                    ],
                ],
            ],
            'purchaseTicket'
        );
    }

    // =========================================================================
    //  TRANSACTION STATUS
    // =========================================================================

    /**
     * Check the status of a CPay transaction.
     */
    public function checkTransactionStatus(string $txnId, ?string $date = null): array
    {
        return $this->get(
            '/api/cpaypayments/transaction-status',
            [
                'requestReference' => $txnId,
                'dateTime'         => $date ?? now()->format('Y-m-d'),
            ],
            'checkTransactionStatus'
        );
    }

    // =========================================================================
    //  INTERNAL HELPERS
    // =========================================================================

    /**
     * Build a standard single-wrapped CPay transaction request body.
     */
    private function buildRequest(string $txnId, string $msisdn, string $amount, string $description): array
    {
        return [
            'extTransactionId' => $txnId,
            'clientCode'       => $this->clientCode,
            'msisdn'           => $msisdn,
            'amount'           => $amount,
            'otp'              => '',
            'shortDescription' => $description,
            'checksum'         => $this->checksum($txnId, $amount, $msisdn),
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => '',
        ];
    }

    /**
     * Generate HMAC-SHA256 checksum.
     * Salt: ExtTransactionId + ClientCode + Amount + MSISDN
     */
    private function checksum(string $txnId, string $amount, string $msisdn): string
    {
        return hash_hmac('sha256', $txnId . $this->clientCode . $amount . $msisdn, $this->secretKey);
    }

    /**
     * Generate a unique transaction ID.
     */
    public function generateTxnId(string $prefix = 'MBILL'): string
    {
        return strtoupper($prefix) . '-' . strtoupper(Str::random(8)) . '-' . time();
    }

    /**
     * Strip phone to 8-digit local format.
     */
    public function msisdn(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '266')) {
            return substr($digits, 3);
        }
        return substr($digits, -8);
    }

    // ── HTTP ─────────────────────────────────────────────────────

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => $this->apiKey,   // NO "Bearer " prefix — per CPay docs
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(30)
            ->retry(1, 500);
    }

    private function post(string $endpoint, array $body, string $ctx): array
    {
        try {
            Log::info("CPayBill::{$ctx} REQUEST", ['endpoint' => $endpoint, 'payload' => $body]);

            $response = $this->client()->post($endpoint, $body);

            Log::info("CPayBill::{$ctx} RESPONSE", [
                'http' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return $this->parseResponse($response, $ctx);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("CPayBill::{$ctx} connection error", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Cannot connect to CPay: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            Log::error("CPayBill::{$ctx} exception", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function get(string $endpoint, array $query, string $ctx): array
    {
        try {
            Log::info("CPayBill::{$ctx} REQUEST", ['endpoint' => $endpoint, 'query' => $query]);

            $response = $this->client()->get($endpoint, $query);

            Log::info("CPayBill::{$ctx} RESPONSE", [
                'http' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error'   => "HTTP {$response->status()}",
                    'data'    => $response->json() ?? [],
                ];
            }

            return [
                'success' => true,
                'data'    => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error("CPayBill::{$ctx} exception", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function parseResponse(\Illuminate\Http\Client\Response $response, string $ctx): array
    {
        $raw  = $response->json();
        $data = $raw['return'] ?? $raw ?? [];

        if (!$response->successful()) {
            $err = $data['description'] ?? $data['Description']
                ?? $data['message'] ?? "HTTP {$response->status()}";

            Log::warning("CPayBill::{$ctx} failed", ['http' => $response->status(), 'err' => $err]);

            return [
                'success'     => false,
                'error'       => $err,
                'status'      => 'FAILED',
                'reason_code' => $data['reasonCode'] ?? $data['ReasonCode'] ?? null,
                'data'        => $data,
            ];
        }

        return [
            'success'      => true,
            'cpay_txn_id'  => $data['cPayTransactionId'] ?? $data['CPayTransactionId'] ?? null,
            'status'       => strtoupper($data['paymentRequestStatus'] ?? $data['PaymentRequestStatus'] ?? 'PROCESSED'),
            'description'  => $data['description'] ?? $data['Description'] ?? 'OK',
            'reason_code'  => $data['reasonCode'] ?? $data['ReasonCode'] ?? null,
            'data'         => $data,
            // Category-specific data extracted from additionalData
            'additional'   => $data['additionalData'] ?? $data['AdditionalData'] ?? null,
        ];
    }
}
