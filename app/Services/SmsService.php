<?php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Log};

/**
 * MyMobileAPI SMS Service
 *
 * Docs:      https://mymobileapi.readme.io/docs/rest
 * REST API:  POST https://rest.mymobileapi.com/v1/bulkmessages
 * Auth:      Basic HTTP — ClientID:APISecret (Base64)
 *
 * .env keys:
 *   BULKSMS_USERNAME = Client ID   (UUID format, e.g. 0d4576e0-...)
 *   BULKSMS_PASSWORD = API Secret  (e.g. SahLZ110hj...)
 *   BULKSMS_SENDER   = Sender name shown on phone (e.g. MyLoan)
 */
class SmsService
{
    private string $clientId;
    private string $apiSecret;
    private string $sender;
    private bool   $enabled;

    private const REST_URL = 'https://rest.mymobileapi.com/v1/bulkmessages';

    public function __construct()
    {
        $this->clientId  = config('services.bulksms.username', '');
        $this->apiSecret = config('services.bulksms.password', '');
        $this->sender    = config('services.bulksms.sender', 'MyLoan');
        $this->enabled   = !empty($this->clientId) && !empty($this->apiSecret);
    }

    /**
     * Send a plain SMS.
     *
     * @param  string $to   e.g. +26658145851 or 26658145851
     * @param  string $body Message text (max 160 chars per segment)
     * @return bool
     */
    public function send(string $to, string $body): bool
    {
        if (!$this->enabled) {
            Log::info('SmsService: disabled (no credentials). Would have sent SMS.', [
                'to' => $to, 'body' => $body,
            ]);
            return false;
        }

        // MyMobileAPI expects digits only, no '+' or spaces
        // e.g.  +26658145851  →  26658145851
        $number = preg_replace('/[^0-9]/', '', $to);

        try {
            $response = Http::withBasicAuth($this->clientId, $this->apiSecret)
                ->timeout(15)
                ->acceptJson()
                ->post(self::REST_URL, [
                    'Messages' => [[
                        'Content'     => $body,
                        'Destination' => $number,
                        'Sender'      => $this->sender,
                    ]],
                ]);

            Log::info('SmsService::send', [
                'to'   => $to,
                'http' => $response->status(),
                'body' => $response->body(),
            ]);

            // 200 or 201 = queued successfully
            if ($response->successful()) {
                return true;
            }

            Log::warning('SmsService::send failed', [
                'http' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('SmsService::send exception', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a 6-digit OTP, store it, and send it via SMS.
     */
    public function sendOtp(string $phone): string
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        \DB::table('phone_otps')->where('phone', $phone)->delete();
        \DB::table('phone_otps')->insert([
            'phone'      => $phone,
            'otp'        => $otp,
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = "Your MyLoan verification code is: {$otp}\nDo not share this. Expires in 10 minutes.";
        $sent    = $this->send($phone, $message);

        if (!$sent) {
            Log::info('SmsService::sendOtp [DEV — OTP not sent via SMS]', [
                'phone' => $phone,
                'otp'   => $otp,
            ]);
        }

        return $otp;
    }

    /**
     * Verify an OTP. Tracks attempts, deletes on success or too many failures.
     */
    public function verifyOtp(string $phone, string $otp): bool
    {
        $record = \DB::table('phone_otps')
            ->where('phone', $phone)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) return false;

        if ($record->attempts >= 5) {
            \DB::table('phone_otps')->where('phone', $phone)->delete();
            return false;
        }

        if ($record->otp !== trim($otp)) {
            \DB::table('phone_otps')->where('phone', $phone)->increment('attempts');
            return false;
        }

        \DB::table('phone_otps')->where('phone', $phone)->delete();
        return true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
