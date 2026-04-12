<?php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Log, Cache};
use App\Models\{Loan, Payment, SystemSetting};

class MpesaService
{
    private string $key;
    private string $secret;
    private string $shortcode;
    private string $passkey;
    private string $baseUrl;
    private string $env;
    private string $initiator;
    private string $b2cShortcode;
    private string $securityCredential;

    public function __construct()
    {
        $this->key                = config('services.mpesa.key');
        $this->secret             = config('services.mpesa.secret');
        $this->shortcode          = config('services.mpesa.shortcode');
        $this->passkey            = config('services.mpesa.passkey');
        $this->env                = config('services.mpesa.env', 'sandbox');
        $this->initiator          = config('services.mpesa.initiator', 'testapi');
        $this->b2cShortcode       = config('services.mpesa.b2c_shortcode', '600000');
        $this->securityCredential = config('services.mpesa.security_credential');

        $this->baseUrl = ($this->env === 'live') 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get OAuth Access Token with caching.
     */
    public function getToken(): ?string
    {
        return Cache::remember('mpesa_access_token', 3300, function () {
            $response = Http::withBasicAuth($this->key, $this->secret)
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->failed()) {
                Log::error('MpesaService::getToken FAILED', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return null;
            }

            return $response->json()['access_token'] ?? null;
        });
    }

    /**
     * Initiate STK Push (Lipa Na M-Pesa Online)
     */
    public function initiateStkPush(Payment $payment, string $phone): array
    {
        $token = $this->getToken();
        if (!$token) return ['success' => false, 'error' => 'Could not authenticate with Safaricom.'];

        $timestamp = date('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $amount    = round($payment->amount); // M-Pesa only accepts integers in some contexts, but usually floats are fine. Let's use round for saftey in repayments.
        $msisdn    = $this->formatPhone($phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $msisdn,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $msisdn,
            'CallBackURL'       => route('webhooks.mpesa.repayment'),
            'AccountReference'  => substr($payment->payment_reference, 0, 12),
            'TransactionDesc'   => 'Loan Repayment'
        ];

        Log::info('MpesaService::initiateStkPush REQUEST', $payload);

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", $payload);

        Log::info('MpesaService::initiateStkPush RESPONSE', [
            'status' => $response->status(),
            'body'   => $response->json()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (($data['ResponseCode'] ?? '') == '0') {
                return [
                    'success' => true,
                    'merchant_request_id' => $data['MerchantRequestID'],
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'message' => $data['CustomerMessage']
                ];
            }
        }

        return [
            'success' => false,
            'error'   => $response->json()['errorMessage'] ?? 'STK Push failed'
        ];
    }

    /**
     * Initiate B2C Disbursement
     */
    public function disburseLoan(Loan $loan, string $phone, string $reference): array
    {
        $token = $this->getToken();
        if (!$token) return ['success' => false, 'error' => 'Could not authenticate with Safaricom.'];

        $msisdn = $this->formatPhone($phone);
        $amount = (float) $loan->principal_amount;

        $payload = [
            'InitiatorName'      => $this->initiator,
            'SecurityCredential' => $this->securityCredential,
            'CommandID'          => 'BusinessPayment',
            'Amount'             => $amount,
            'PartyA'             => $this->b2cShortcode,
            'PartyB'             => $msisdn,
            'Remarks'            => 'Disbursement ' . $loan->loan_number,
            'QueueTimeOutURL'    => route('webhooks.mpesa.disburse.timeout'),
            'ResultURL'          => route('webhooks.mpesa.disburse.result'),
            'Occasion'           => 'LoanDisbursement'
        ];

        Log::info('MpesaService::disburseLoan REQUEST', $payload);

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/mpesa/b2c/v1/paymentrequest", $payload);

        Log::info('MpesaService::disburseLoan RESPONSE', [
            'status' => $response->status(),
            'body'   => $response->json()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (($data['ResponseCode'] ?? '') == '0') {
                return [
                    'success' => true,
                    'conversation_id' => $data['ConversationID'],
                    'originator_conversation_id' => $data['OriginatorConversationID'],
                    'message' => $data['ResponseDescription']
                ];
            }
        }

        return [
            'success' => false,
            'error'   => $response->json()['errorMessage'] ?? 'Disbursement failed'
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->key) && !empty($this->secret) && !empty($this->securityCredential);
    }

    /**
     * Formats phone to 2547XXXXXXXX
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        
        if (strlen($phone) == 9) {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}
