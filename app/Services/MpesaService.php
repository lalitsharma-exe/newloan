<?php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Log, Cache};
use App\Models\{Loan, Payment, SystemSetting};

/**
 * MpesaService for Vodacom Lesotho (Open API)
 */
class MpesaService
{
    private string $apiKey;
    private string $publicKey;
    private string $market;
    private string $host;
    private string $env;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey    = config('services.mpesa.api_key');
        $this->publicKey = config('services.mpesa.public_key');
        $this->market    = config('services.mpesa.market', 'vodacomLES');
        $this->host      = config('services.mpesa.host', 'openapi.m-pesa.com');
        $this->env       = config('services.mpesa.env', 'sandbox');

        $this->baseUrl = "https://{$this->host}/{$this->env}/ipg/v2/{$this->market}";
    }

    /**
     * Generate Session ID (Access Token) using RSA Encrypted API Key.
     */
    public function getSessionID(): ?string
    {
        return Cache::remember('mpesa_session_id', 3300, function () {
            try {
                $encryptedKey = $this->encryptValue($this->apiKey, $this->publicKey);
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $encryptedKey,
                    'Content-Type'  => 'application/json',
                    'Origin'        => '*',
                ])->get("{$this->baseUrl}/getSession/");

                if ($response->failed()) {
                    Log::error('MpesaService::getSessionID FAILED', [
                        'status' => $response->status(),
                        'body'   => $response->body()
                    ]);
                    return null;
                }

                return $response->json()['output_SessionID'] ?? null;
            } catch (\Exception $e) {
                Log::error('MpesaService::getSessionID EXCEPTION: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * RSA Encryption Helper.
     */
    private function encryptValue(string $value, string $pubKeyBase64): string
    {
        $pubKeyPem = "-----BEGIN PUBLIC KEY-----\n" . 
                     wordwrap($pubKeyBase64, 64, "\n", true) . 
                     "\n-----END PUBLIC KEY-----";
        
        $encrypted = '';
        if (!openssl_public_encrypt($value, $encrypted, $pubKeyPem, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('RSA encryption failed: ' . openssl_error_string());
        }
        
        return base64_encode($encrypted);
    }

    /**
     * Initiate C2B Payment (Single Stage) - Repayments
     */
    public function initiateStkPush(Payment $payment, string $phone): array
    {
        $sessionId = $this->getSessionID();
        if (!$sessionId) return ['success' => false, 'error' => 'Could not authenticate with Vodacom M-Pesa.'];

        $encryptedSessionId = $this->encryptValue($sessionId, $this->publicKey);
        $amount = number_format($payment->amount, 1, '.', '');
        $msisdn = $this->formatPhone($phone);
        $convId = preg_replace('/[^A-Za-z0-9]/', '', $payment->payment_reference);
        
        $payload = [
            'input_Amount'                   => $amount,
            'input_Country'                  => 'LES',
            'input_Currency'                 => 'LSL',
            'input_CustomerMSISDN'           => $msisdn,
            'input_ServiceProviderCode'      => config('services.mpesa.shortcode', '000000'),
            'input_ThirdPartyConversationID' => $convId,
            'input_TransactionReference'     => substr($convId, 0, 10),
            'input_PurchasedItemsDesc'       => 'Loan Repayment'
        ];

        Log::info('MpesaService::initiateStkPush REQUEST', $payload);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $encryptedSessionId,
            'Content-Type'  => 'application/json',
            'Origin'        => '*',
        ])->post("{$this->baseUrl}/c2bPayment/singleStage/", $payload);

        Log::info('MpesaService::initiateStkPush RESPONSE', [
            'status' => $response->status(),
            'body'   => $response->json()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (($data['output_ResponseCode'] ?? '') == 'INS-0') {
                return [
                    'success' => true,
                    'transaction_id' => $data['output_TransactionID'] ?? null,
                    'conversation_id' => $data['output_ConversationID'] ?? null,
                    'message' => $data['output_ResponseDesc'] ?? 'Request accepted.'
                ];
            }
        }

        return [
            'success' => false,
            'error'   => $response->json()['output_ResponseDesc'] ?? 'Payment failed'
        ];
    }

    /**
     * Initiate B2C Disbursement
     */
    public function disburseLoan(Loan $loan, string $phone, string $reference): array
    {
        $sessionId = $this->getSessionID();
        if (!$sessionId) return ['success' => false, 'error' => 'Could not authenticate with Vodacom M-Pesa.'];

        $encryptedSessionId = $this->encryptValue($sessionId, $this->publicKey);
        $msisdn = $this->formatPhone($phone);
        $amount = number_format($loan->principal_amount, 1, '.', '');
        $txnRef = preg_replace('/[^A-Za-z0-9]/', '', $loan->loan_number);
        $convId = preg_replace('/[^A-Za-z0-9]/', '', $reference);

        $payload = [
            'input_Amount'                   => $amount,
            'input_Country'                  => 'LES',
            'input_Currency'                 => 'LSL',
            'input_CustomerMSISDN'           => $msisdn,
            'input_ServiceProviderCode'      => config('services.mpesa.shortcode', '000000'),
            'input_ThirdPartyConversationID' => $convId,
            'input_TransactionReference'     => substr($txnRef, 0, 10),
            'input_PurchasedItemsDesc'       => 'Loan Disbursement ' . $loan->loan_number
        ];

        Log::info('MpesaService::disburseLoan REQUEST', $payload);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $encryptedSessionId,
            'Content-Type'  => 'application/json',
            'Origin'        => '*',
        ])->post("{$this->baseUrl}/b2cPayment/", $payload);

        Log::info('MpesaService::disburseLoan RESPONSE', [
            'status' => $response->status(),
            'body'   => $response->json()
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (($data['output_ResponseCode'] ?? '') == 'INS-0') {
                return [
                    'success' => true,
                    'transaction_id' => $data['output_TransactionID'] ?? null,
                    'conversation_id' => $data['output_ConversationID'] ?? null,
                    'message' => $data['output_ResponseDesc'] ?? 'Disbursement accepted.'
                ];
            }
        }

        return [
            'success' => false,
            'error'   => $response->json()['output_ResponseDesc'] ?? 'Disbursement failed'
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->publicKey);
    }

    /**
     * Formats phone to Lesotho standard (266XXXXXXXX)
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If it's a sandbox test number (12 digits starting with 0000), leave it as is
        if (strlen($phone) == 12 && str_starts_with($phone, '0000')) {
            return $phone;
        }

        // Standard Lesotho logic: ensure it starts with 266
        if (str_starts_with($phone, '266') && strlen($phone) == 11) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            $phone = '266' . substr($phone, 1);
        }
        
        if (strlen($phone) == 8) {
            $phone = '266' . $phone;
        }

        return $phone;
    }
}
