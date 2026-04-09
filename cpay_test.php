#!/usr/bin/env php
<?php
/**
 * CPay UAT Direct Test Script v2.0
 * Usage: php cpay_test.php [command]
 *
 *   checksum   verify checksum calculation matches CPay
 *   mobile     initiate OTP payment (step 1)
 *   confirm    confirm OTP (step 2) — set FIXED_TXN_ID + TEST_OTP first
 *   async      USSD push async payment
 *   card       card payment (returns HTML redirect)
 *   status     check transaction status (set FIXED_TXN_ID first)
 *   list       list recent transactions for this merchant
 *   disburse   external disbursement (MPesa)
 *   wallet     CPay wallet top-up advance (with KYC)
 *   all        run checksum + mobile + async
 */

// ================================================================
// CONFIG — reads live credentials from .env
// ================================================================
function loadEnv(string $path): array {
    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // Strip surrounding quotes
        if (preg_match('/^"(.*)"$|^\'(.*)\'$/s', $val, $m)) {
            $val = $m[1] !== '' ? $m[1] : ($m[2] ?? '');
        }
        $env[$key] = $val;
    }
    return $env;
}
$dotenv = loadEnv(__DIR__ . '/.env');

define('BASE_URL',      rtrim($dotenv['CPAY_LIVE_URL'] ?? 'https://prod.chaperone.co.ls:5700/api', '/api'));
define('API_KEY',       $dotenv['CPAY_API_KEY']       ?? '');
define('CLIENT_CODE',   $dotenv['CPAY_CLIENT_CODE']   ?? '');
define('SECRET_KEY',    $dotenv['CPAY_SECRET_KEY']    ?? '');
define('MERCHANT_CODE', $dotenv['CPAY_MERCHANT_CODE'] ?? '');

// 8-digit local MSISDN — real live registered number
define('TEST_MSISDN',  '53797734');
define('TEST_AMOUNT',  '10.00');

// For confirm test: paste OTP received, and the extTransactionId from mobile run
define('TEST_OTP',     '123456');
define('FIXED_TXN_ID', 'AUTO');

// Public HTTPS callback URL
define('REDIRECT_URL', rtrim($dotenv['APP_URL'] ?? 'http://localhost:8000', '/') . '/webhooks/payment');
// ================================================================

$test = $argv[1] ?? 'help';

echo "\n";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║      CPay UAT Test — v2.0 (MYLOAN18374)         ║\n";
echo "╚══════════════════════════════════════════════════╝\n";
echo "  Client Code  : " . CLIENT_CODE   . "\n";
echo "  Merchant Code: " . MERCHANT_CODE . "\n";
echo "  MSISDN       : " . TEST_MSISDN   . "\n";
echo "  Amount       : " . TEST_AMOUNT   . "\n";
echo "  Test         : " . $test         . "\n\n";

// ================================================================
// HELPERS
// ================================================================

function txnId(string $prefix = 'TEST'): string
{
    return strtoupper($prefix) . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 8)) . '-' . time();
}

function fixedOrNew(string $prefix = 'TEST'): string
{
    return FIXED_TXN_ID === 'AUTO' ? txnId($prefix) : FIXED_TXN_ID;
}

/**
 * CHECKSUM: HMAC-SHA256
 * Salt (initiate):  txnId + clientCode + amount + msisdn
 * Salt (confirm):   txnId + clientCode + amount + msisdn + otp
 */
function checksumCalc(string $txnId, string $amount, string $msisdn, string $otp = ''): string
{
    $salt = $txnId . CLIENT_CODE . $amount . $msisdn . $otp;
    $hash = hash_hmac('sha256', $salt, SECRET_KEY);
    echo "  Checksum salt : \"{$salt}\"\n";
    echo "  Checksum hash : {$hash}\n";
    return $hash;
}

function req(string $method, string $endpoint, array $body = [], array $query = []): mixed
{
    $url = BASE_URL . $endpoint;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    $bodyJson = $body ? json_encode($body, JSON_PRETTY_PRINT) : '';

    echo "  ┌─ REQUEST ──────────────────────────────────────\n";
    echo "  │ {$method} {$url}\n";
    echo "  │ Authorization: " . API_KEY . "\n";
    if ($bodyJson) {
        foreach (explode("\n", $bodyJson) as $line) {
            echo "  │   {$line}\n";
        }
    }
    echo "  └────────────────────────────────────────────────\n\n";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $bodyJson ?: null,
    ]);

    $raw     = curl_exec($ch);
    $http    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    echo "  ┌─ RESPONSE ─────────────────────────────────────\n";
    echo "  │ HTTP: {$http}\n";

    if ($curlErr) {
        echo "  │ ❌ cURL Error: {$curlErr}\n";
        echo "  └────────────────────────────────────────────────\n\n";
        return null;
    }

    // Detect HTML (card payment)
    if (trim($raw) && str_starts_with(trim($raw), '<')) {
        echo "  │ [HTML response — card redirect page, " . strlen($raw) . " bytes]\n";
        if (preg_match('/content=["\']0;url=([^"\']+)["\']/', $raw, $m)) {
            echo "  │ ✅ Redirect URL: {$m[1]}\n";
        } elseif (preg_match('/action=["\']([^"\']+)["\']/', $raw, $m)) {
            echo "  │ 📝 Form action: {$m[1]}\n";
        }
        echo "  └────────────────────────────────────────────────\n\n";
        return $raw;
    }

    $decoded = json_decode($raw, true);
    $pretty  = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT) : $raw;
    foreach (explode("\n", $pretty) as $line) {
        echo "  │ {$line}\n";
    }

    $data   = $decoded['return'] ?? $decoded ?? [];
    $status = $data['paymentRequestStatus'] ?? $data['PaymentRequestStatus']
           ?? $data['statusCode']           ?? $data['StatusCode']   ?? '—';
    $desc   = $data['description'] ?? $data['Description'] ?? '—';
    $reason = $data['reasonCode']  ?? $data['ReasonCode']  ?? '—';
    $cid    = $data['cPayTransactionId']  ?? $data['CPayTransactionId']  ?? '—';

    echo "  ├─ PARSED ───────────────────────────────────────\n";
    echo "  │ Status:      {$status}\n";
    echo "  │ Description: {$desc}\n";
    echo "  │ ReasonCode:  {$reason}\n";
    if ($cid !== '—') echo "  │ CPay TxnId:  {$cid}\n";

    if ($http >= 200 && $http < 300) {
        echo "  │ ✅ SUCCESS\n";
    } else {
        echo "  │ ❌ FAILED (HTTP {$http})\n";
        if ($reason === 'validationFailure' || str_contains(strtolower($desc), 'does not exist')) {
            echo "  │ 💡 MSISDN not registered in CPay UAT.\n";
            echo "  │    Email: apisupport@chaperone.co.ls\n";
            echo "  │    Ask:   Register " . TEST_MSISDN . " for client MYLOAN18374\n";
        } elseif ($reason === 'checksumError') {
            echo "  │ 💡 Checksum mismatch. Run: php cpay_test.php checksum\n";
        } elseif ($http === 401) {
            echo "  │ 💡 Invalid API Key — check API_KEY constant.\n";
        }
    }
    echo "  └────────────────────────────────────────────────\n\n";

    return $decoded;
}

// ================================================================
// TEST FUNCTIONS
// ================================================================

function testChecksum(): void
{
    echo "━━━ TEST: Checksum Verification ━━━\n\n";
    $id = txnId('CHKSUM');
    echo "  TxnId: {$id}\n";
    $ourHash = checksumCalc($id, TEST_AMOUNT, TEST_MSISDN);
    echo "\n  → Asking CPay to compute same checksum...\n\n";

    req('POST', '/api/cpaypayments/getchecksum', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => TEST_MSISDN,
            'otp'              => '',
            'amount'           => TEST_AMOUNT,
            'shortDescription' => '',
            'checksum'         => '',
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => '',
            'redirectUrl'      => '',
        ],
    ]);

    echo "  Our hash  : {$ourHash}\n";
    echo "  CPay hash : (see response above)\n";
    echo "  ✅ = match → checksum logic correct\n";
    echo "  ❌ = mismatch → wrong secret key or salt order\n\n";
}

function testMobile(): void
{
    echo "━━━ TEST: OTP Payment Initiation ━━━\n\n";
    $id = txnId('OTP');
    echo "  TxnId: {$id}\n";
    $cs = checksumCalc($id, TEST_AMOUNT, TEST_MSISDN);
    echo "\n";

    req('POST', '/api/cpaypayments/payment', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => TEST_MSISDN,
            'amount'           => TEST_AMOUNT,
            'otp'              => '',
            'shortDescription' => 'Loan repayment test',
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => REDIRECT_URL,
        ],
    ]);

    echo "  ↳ TxnId: {$id}\n";
    echo "  ↳ If reasonCode=otpSend: OTP was sent to " . TEST_MSISDN . "\n";
    echo "  ↳ Next: set FIXED_TXN_ID='{$id}' and TEST_OTP to the received OTP\n";
    echo "          then run: php cpay_test.php confirm\n\n";
}

function testConfirm(): void
{
    echo "━━━ TEST: OTP Confirmation ━━━\n\n";
    $id  = fixedOrNew('OTP');
    $otp = TEST_OTP;

    echo "  TxnId: {$id}\n";
    echo "  OTP:   {$otp}\n";

    if (FIXED_TXN_ID === 'AUTO') {
        echo "\n  ⚠️  FIXED_TXN_ID is AUTO — set it to the extTransactionId from the mobile run.\n\n";
    }

    $cs = checksumCalc($id, TEST_AMOUNT, TEST_MSISDN, $otp);
    echo "\n";

    req('POST', '/api/cpaypayments/confirm', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => TEST_MSISDN,
            'amount'           => TEST_AMOUNT,
            'otp'              => $otp,
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'redirectUrl'      => REDIRECT_URL,
        ],
    ]);
}

function testAsync(): void
{
    echo "━━━ TEST: Async USSD Payment ━━━\n\n";
    echo "  Sends a USSD push to the customer's phone.\n";
    echo "  Customer selects 'Pay Merchant' to confirm.\n";
    echo "  ⚠️  redirectUrl must be public HTTPS (use ngrok for local dev).\n\n";

    $id = txnId('ASYNC');
    echo "  TxnId: {$id}\n";
    $cs = checksumCalc($id, TEST_AMOUNT, TEST_MSISDN);
    echo "\n";

    req('POST', '/api/cpaypayments/paymentrequest/async/transactions', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => TEST_MSISDN,
            'amount'           => TEST_AMOUNT,
            'otp'              => '',
            'shortDescription' => 'Async loan repayment test',
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => REDIRECT_URL,
        ],
    ]);

    echo "  ↳ TxnId: {$id}\n";
    echo "  ↳ If open: USSD push sent. Set FIXED_TXN_ID='{$id}' then:\n";
    echo "             php cpay_test.php status\n\n";
}

function testCard(): void
{
    echo "━━━ TEST: Card Payment ━━━\n\n";
    echo "  Returns HTML/redirect — open in browser to complete 3DS.\n";
    echo "  ⚠️  redirectUrl must be public HTTPS.\n";
    echo "  ⚠️  Card payments must be enabled for MYLOAN18374 in UAT.\n\n";

    $id = txnId('CARD');
    echo "  TxnId: {$id}\n";
    $cs = checksumCalc($id, TEST_AMOUNT, TEST_MSISDN);
    echo "\n";

    req('POST', '/api/cpaypayments/payment', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => TEST_MSISDN,
            'amount'           => TEST_AMOUNT,
            'otp'              => '',
            'shortDescription' => 'Card payment test',
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => REDIRECT_URL,
        ],
    ], ['cardPayment' => 'true', 'rememberMe' => 'false']);
}

function testStatus(): void
{
    echo "━━━ TEST: Transaction Status ━━━\n\n";
    $id   = fixedOrNew('QUERY');
    $date = date('Y-m-d');

    echo "  TxnId: {$id}\n";
    echo "  Date:  {$date}\n\n";

    if (FIXED_TXN_ID === 'AUTO') {
        echo "  ⚠️  Set FIXED_TXN_ID to a real extTransactionId.\n\n";
    }

    req('GET', '/api/cpaypayments/transaction-status', [], [
        'requestReference' => $id,
        'dateTime'         => $date,
    ]);
}

function testList(): void
{
    echo "━━━ TEST: List Transactions ━━━\n\n";
    req('GET', '/api/cpaypayments/payment/request/transactions', [], [
        'merchantCode' => MERCHANT_CODE,
        'pageSize'     => 10,
        'page'         => 0,
        'orderBy'      => 'dateDesc',
    ]);
}

function testDisburse(): void
{
    echo "━━━ TEST: External Disbursement (MPesa) ━━━\n\n";
    $id      = txnId('DISB');
    $msisdn  = '+266' . TEST_MSISDN;
    $msisdn8 = TEST_MSISDN;

    echo "  TxnId:   {$id}\n";
    echo "  MSISDN:  {$msisdn} (body) | {$msisdn8} (query param)\n";
    $cs = checksumCalc($id, TEST_AMOUNT, $msisdn);
    echo "\n";

    req('POST', '/api/disbursements/external-payment', [
        'transactionRequest' => [
            'transactionRequest' => [
                'extTransactionId' => $id,
                'clientCode'       => CLIENT_CODE,
                'msisdn'           => $msisdn,
                'amount'           => TEST_AMOUNT,
                'shortDescription' => 'Test disbursement MPesa',
                'checksum'         => $cs,
                'currency'         => 'LSL',
                'otp'              => '',
                'redirectUrl'      => REDIRECT_URL,
                'additionalData'   => 'loan:TEST-001',
            ],
        ],
    ], ['destinationOperator' => 'mpesa', 'destinationWalletNumber' => $msisdn8]);
}

function testWallet(): void
{
    echo "━━━ TEST: Wallet Top-Up Advance (CPay + KYC) ━━━\n\n";
    $id      = txnId('WALLT');
    $msisdn  = '+266' . TEST_MSISDN;
    $msisdn8 = TEST_MSISDN;

    echo "  TxnId:   {$id}\n";
    echo "  MSISDN:  {$msisdn8} (body) | {$msisdn} (checksum salt)\n";
    $cs = checksumCalc($id, TEST_AMOUNT, $msisdn);
    echo "\n";

    req('POST', '/api/disbursements/wallet-topup-advance', [
        'transactionRequest' => [
            'transactionRequest' => [
                'extTransactionId' => $id,
                'clientCode'       => CLIENT_CODE,
                'msisdn'           => $msisdn8,
                'amount'           => TEST_AMOUNT,
                'shortDescription' => 'Test wallet top-up',
                'checksum'         => $cs,
                'currency'         => 'LSL',
                'redirectUrl'      => REDIRECT_URL,
                'additionalData'   => [
                    'recipientKyc' => [
                        'idDocument' => [[
                            'idType'        => 'ID',
                            'idNumber'      => 'TEST123456789',
                            'expiryDate'    => '2030-12-31',
                            'issuerCountry' => 'LS',
                        ]],
                        'firstName'     => 'Test',
                        'middleName'    => '',
                        'lastName'      => 'User',
                        'fullName'      => 'Test User',
                        'gender'        => '',
                        'sourceOfFunds' => 'Loan Disbursement',
                    ],
                ],
            ],
        ],
    ], ['destinationOperator' => 'CPAY', 'destinationWalletNumber' => $msisdn8]);
}

function showHelp(string $test): void
{
    if ($test !== 'help') {
        echo "  ❌ Unknown command: {$test}\n\n";
    }
    echo "  Usage: php cpay_test.php <command>\n\n";
    echo "  Commands:\n";
    echo "    checksum   verify our checksum vs CPay's\n";
    echo "    mobile     initiate OTP payment (step 1)\n";
    echo "    confirm    confirm OTP (step 2) — set FIXED_TXN_ID + TEST_OTP\n";
    echo "    async      USSD push async payment\n";
    echo "    card       card payment (HTML redirect)\n";
    echo "    status     check transaction status\n";
    echo "    list       list recent transactions\n";
    echo "    disburse   external disbursement (MPesa)\n";
    echo "    wallet     CPay wallet top-up advance\n";
    echo "    all        run checksum + mobile + async\n\n";
}

// ================================================================
// RUNNER
// ================================================================
match ($test) {
    'checksum' => testChecksum(),
    'mobile'   => testMobile(),
    'confirm'  => testConfirm(),
    'async'    => testAsync(),
    'card'     => testCard(),
    'status'   => testStatus(),
    'list'     => testList(),
    'disburse' => testDisburse(),
    'wallet'   => testWallet(),
    'all'      => (function () {
        testChecksum();
        testMobile();
        testAsync();
    })(),
    default    => showHelp($test),
};