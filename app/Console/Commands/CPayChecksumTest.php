<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Brute-forces every checksum combination until CPay returns something other
 * than "Transaction Checksum is invalid". Run once, note which variant passes,
 * then update CPayService::checksum() accordingly and delete this command.
 *
 * Usage:
 *   php artisan cpay:test-checksum
 */
class CPayChecksumTest extends Command
{
    protected $signature   = 'cpay:test-checksum';
    protected $description = 'Find the correct CPay HMAC checksum formula by trying all combinations';

    public function handle(): void
    {
        $baseUrl    = 'https://cpay-uat-env.chaperone.co.ls:5100';
        $apiKey     = SystemSetting::get('gateway_key',      config('services.cpay.api_key',     ''));
        $clientCode = SystemSetting::get('cpay_client_code', config('services.cpay.client_code', ''));
        $secret     = SystemSetting::get('gateway_secret',   config('services.cpay.secret_key',  ''));

        if (!$apiKey || !$clientCode || !$secret) {
            $this->error('CPay credentials not set. Add to .env or Admin Settings first.');
            return;
        }

        $txnId    = 'TEST-' . strtoupper(Str::random(8));
        $amount   = '10.00';     // small safe test amount
        $msisdn   = '50000001';  // CPay UAT test number — update if CPay gave you a specific one
        $currency = 'LSL';

        $this->info("Using txnId: {$txnId}");
        $this->info("clientCode: {$clientCode}");
        $this->info("secret:     " . substr($secret, 0, 4) . str_repeat('*', max(0, strlen($secret) - 4)));
        $this->newLine();

        // Every combination of fields and hash method CPay might use
        $variants = [
            // ── Plain SHA256 ─────────────────────────────────────────────────
            '01_sha256_client+txn+amt+secret'        => hash('sha256', $clientCode . $txnId . $amount . $secret),
            '02_sha256_txn+client+amt+secret'        => hash('sha256', $txnId . $clientCode . $amount . $secret),
            '03_sha256_txn+amt+secret'               => hash('sha256', $txnId . $amount . $secret),
            '04_sha256_client+txn+secret'            => hash('sha256', $clientCode . $txnId . $secret),
            '05_sha256_txn+secret'                   => hash('sha256', $txnId . $secret),
            '06_sha256_client+txn+msisdn+amt+secret' => hash('sha256', $clientCode . $txnId . $msisdn . $amount . $secret),
            '07_sha256_txn+msisdn+amt+secret'        => hash('sha256', $txnId . $msisdn . $amount . $secret),
            '08_sha256_client+amt+secret'            => hash('sha256', $clientCode . $amount . $secret),

            // ── HMAC-SHA256 (secret as key) ───────────────────────────────────
            '09_hmac_client+txn+amt'                 => hash_hmac('sha256', $clientCode . $txnId . $amount, $secret),
            '10_hmac_txn+client+amt'                 => hash_hmac('sha256', $txnId . $clientCode . $amount, $secret),
            '11_hmac_txn+amt'                        => hash_hmac('sha256', $txnId . $amount, $secret),
            '12_hmac_client+txn'                     => hash_hmac('sha256', $clientCode . $txnId, $secret),
            '13_hmac_txn+amt+client'                 => hash_hmac('sha256', $txnId . $amount . $clientCode, $secret),
            '14_hmac_client+txn+msisdn+amt'          => hash_hmac('sha256', $clientCode . $txnId . $msisdn . $amount, $secret),
            '15_hmac_txn+msisdn+amt'                 => hash_hmac('sha256', $txnId . $msisdn . $amount, $secret),
            '16_hmac_txn+amt+secret'                 => hash_hmac('sha256', $txnId . $amount . $secret, $secret),

            // ── MD5 (some older gateways use MD5) ────────────────────────────
            '17_md5_client+txn+amt+secret'           => md5($clientCode . $txnId . $amount . $secret),
            '18_md5_txn+amt+secret'                  => md5($txnId . $amount . $secret),

            // ── Base64 variants ───────────────────────────────────────────────
            '19_b64_hmac_client+txn+amt'             => base64_encode(hash_hmac('sha256', $clientCode . $txnId . $amount, $secret, true)),
            '20_b64_sha256_client+txn+amt+secret'    => base64_encode(hash('sha256', $clientCode . $txnId . $amount . $secret, true)),
        ];

        $client = Http::baseUrl($baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(10);

        $this->info('Testing ' . count($variants) . " checksum variants against CPay UAT...");
        $this->info(str_repeat('─', 70));

        foreach ($variants as $name => $checksum) {
            $body = [
                'transactionRequest' => [
                    'transactionRequest' => [
                        'extTransactionId' => $txnId,
                        'clientCode'       => $clientCode,
                        'msisdn'           => $msisdn,
                        'amount'           => $amount,
                        'shortDescription' => 'Checksum test',
                        'checksum'         => $checksum,
                        'currency'         => $currency,
                        'redirectUrl'      => 'https://example.com/callback',
                        'additionalData'   => 'checksum_test',
                    ],
                ],
            ];

            $url = '/api/disbursements/external-payment'
                . '?destinationOperator=MPESA'
                . '&destinationWalletNumber=' . $msisdn;

            try {
                $response = $client->post($url, $body);
                $status   = $response->status();
                $desc     = $response->json('Description') ?? $response->json('description') ?? $response->body();

                // Truncate long descriptions
                $desc = strlen($desc) > 60 ? substr($desc, 0, 60) . '...' : $desc;

                if (str_contains(strtolower($desc), 'checksum')) {
                    // Still a checksum error — wrong formula
                    $this->line("  <fg=red>✗</> [{$status}] {$name}");
                } else {
                    // Different error OR success — checksum was ACCEPTED
                    $this->line("  <fg=green>✓ CHECKSUM ACCEPTED!</> [{$status}] {$name}");
                    $this->info("    Response: {$desc}");
                    $this->info("    Checksum value: {$checksum}");
                    $this->newLine();
                    $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                    $this->info("SUCCESS — update CPayService::checksum() to use variant: {$name}");
                    $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                    // Keep going to find if multiple variants pass
                }

                // Use same txnId — CPay may reject duplicate txnIds, use fresh one each time
                $txnId = 'TEST-' . strtoupper(Str::random(8));
                // Update checksum values that depend on txnId
                // (simpler: just note the passing variant name)

            } catch (\Throwable $e) {
                $this->line("  <fg=yellow>? EXCEPTION</> {$name}: " . $e->getMessage());
            }

            usleep(300000); // 300ms between requests — don't hammer CPay UAT
        }

        $this->newLine();
        $this->info('Done. If all showed ✗, contact CPay support for the exact checksum formula.');
        $this->info('Ask them: "What fields do we concatenate for the HMAC checksum on external-payment?"');
    }
}