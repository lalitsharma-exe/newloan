#!/usr/bin/env php
<?php

/**
 * CPay Direct Test Script
 * Run: php cpay_test.php [test]
 *
 * Tests:
 *   php cpay_test.php mobile     — initiate mobile money repayment (OTP)
 *   php cpay_test.php confirm    — confirm OTP (edit $otp below first)
 *   php cpay_test.php card       — initiate card payment
 *   php cpay_test.php checksum   — verify checksum via CPay's own endpoint
 *   php cpay_test.php status     — query transaction status
 *   php cpay_test.php disburse   — external disbursement (MPesa)
 *   php cpay_test.php wallet     — wallet top-up advance
 *   php cpay_test.php all        — run mobile + checksum + status
 */

// ============================================================
// HARDCODED CONFIG — edit these
// ============================================================
const BASE_URL    = 'https://cpay-uat-env.chaperone.co.ls:5100';
const API_KEY     = 'XUCZxmSxQ10qkCRmx4wS9fflZjTnvlbTWlwYzcI4mO4=';
const CLIENT_CODE = 'MYLOAN_LTD8465';
const SECRET_KEY  = '6vmQlo';

// Test MSISDN — 8-digit format as confirmed by CPay team
// Replace with the number CPay registers for you in UAT
const TEST_MSISDN = '50123456';

// For confirm test — paste the OTP CPay SMS'd to TEST_MSISDN
const TEST_OTP    = '123456';

// Transaction IDs — auto-generated, but you can hardcode for status/confirm tests
const FIXED_TXN_ID = 'TEST-' . TEST_MSISDN . '-' . '001';

// Loan details for disbursement tests
const LOAN_NUMBER = 'LOAN-TEST-001';
const TEST_AMOUNT = '10.00';   // Keep small for sandbox

// Redirect URL — must be publicly reachable for card payments
// Use ngrok locally: ngrok http 8000 → paste the https URL here
const REDIRECT_URL = 'https://your-ngrok-url.ngrok.io/webhooks/payment';
// ============================================================

$test = $argv[1] ?? 'mobile';

echo "\n";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║         CPay UAT Direct Test — v1.1              ║\n";
echo "╚══════════════════════════════════════════════════╝\n";
echo "Base URL:    " . BASE_URL    . "\n";
echo "Client Code: " . CLIENT_CODE . "\n";
echo "MSISDN:      " . TEST_MSISDN . "\n";
echo "Amount:      " . TEST_AMOUNT . "\n";
echo "Test:        " . $test       . "\n\n";

// ============================================================
// HELPERS
// ============================================================

function txnId(string $prefix = 'TEST'): string
{
    return strtoupper($prefix) . '-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . time();
}

function checksum(string $txnId, string $amount, string $msisdn, string $otp = ''): string
{
    $salt = $txnId . CLIENT_CODE . $amount . $msisdn . $otp;
    $hash = hash_hmac('sha256', $salt, SECRET_KEY);
    echo "  Checksum salt : \"{$salt}\"\n";
    echo "  Checksum hash : {$hash}\n";
    return $hash;
}

function request(string $method, string $endpoint, array $body = [], array $query = []): void
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
        echo "  │ Body:\n";
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
        CURLOPT_SSL_VERIFYPEER => false,   // UAT cert may be self-signed
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $bodyJson ?: null,
    ]);

    $raw        = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError  = curl_error($ch);
    curl_close($ch);

    echo "  ┌─ RESPONSE ─────────────────────────────────────\n";
    echo "  │ HTTP Status: {$httpStatus}\n";

    if ($curlError) {
        echo "  │ cURL Error: {$curlError}\n";
        echo "  └────────────────────────────────────────────────\n\n";
        return;
    }

    $decoded = json_decode($raw, true);
    $pretty  = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT) : $raw;
    foreach (explode("\n", $pretty) as $line) {
        echo "  │ {$line}\n";
    }

    $status = $decoded['paymentRequestStatus']
        ?? $decoded['PaymentRequestStatus']
        ?? $decoded['StatusCode']
        ?? $decoded['statusCode']
        ?? '—';
    $desc   = $decoded['Description'] ?? $decoded['description'] ?? '—';
    $reason = $decoded['ReasonCode']  ?? $decoded['reasonCode']  ?? '—';

    echo "  ├─ PARSED ───────────────────────────────────────\n";
    echo "  │ Status:      {$status}\n";
    echo "  │ Description: {$desc}\n";
    echo "  │ ReasonCode:  {$reason}\n";

    if ($httpStatus >= 200 && $httpStatus < 300) {
        echo "  │ ✓ SUCCESS\n";
    } else {
        echo "  │ ✗ FAILED\n";
    }
    echo "  └────────────────────────────────────────────────\n\n";
}

// ============================================================
// TESTS
// ============================================================

function testMobileMoney(): void
{
    echo "━━━ TEST: Mobile Money Repayment (OTP initiation) ━━━\n\n";
    $id     = txnId('MOBILE');
    $amount = TEST_AMOUNT;
    $msisdn = TEST_MSISDN;

    echo "  TxnId: {$id}\n";
    $cs = checksum($id, $amount, $msisdn);
    echo "\n";

    request('POST', '/api/cpaypayments/payment', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => $msisdn,
            'amount'           => $amount,
            'otp'              => '',
            'shortDescription' => 'Test repayment',
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => REDIRECT_URL,
        ],
    ]);

    echo "  → If successful, CPay will SMS an OTP to " . TEST_MSISDN . "\n";
    echo "  → Edit TEST_OTP at the top of this file, then run: php cpay_test.php confirm\n\n";
}

function testConfirmOtp(): void
{
    echo "━━━ TEST: Confirm OTP ━━━\n\n";
    // Use FIXED_TXN_ID so you can match it to the initiation
    $id     = FIXED_TXN_ID;
    $amount = TEST_AMOUNT;
    $msisdn = TEST_MSISDN;
    $otp    = TEST_OTP;

    echo "  TxnId: {$id}\n";
    echo "  OTP:   {$otp}\n";
    $cs = checksum($id, $amount, $msisdn, $otp);
    echo "\n";

    request('POST', '/api/cpaypayments/confirm', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => $msisdn,
            'amount'           => $amount,
            'otp'              => $otp,
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'redirectUrl'      => REDIRECT_URL,
        ],
    ]);
}

function testCard(): void
{
    echo "━━━ TEST: Card Payment ━━━\n\n";
    echo "  NOTE: 'Client Account not found' means your clientCode is not enabled\n";
    echo "  for card payments in UAT. Ask CPay to enable card on MYLOAN_LTD8465.\n\n";
    echo "  NOTE: redirectUrl MUST be a public HTTPS URL (not 127.0.0.1).\n";
    echo "  Use ngrok: ngrok http 8000 → paste URL into REDIRECT_URL constant.\n\n";

    $id     = txnId('CARD');
    $amount = TEST_AMOUNT;
    $msisdn = TEST_MSISDN;

    echo "  TxnId: {$id}\n";
    $cs = checksum($id, $amount, $msisdn);
    echo "\n";

    request('POST', '/api/cpaypayments/payment', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => $msisdn,
            'amount'           => $amount,
            'otp'              => '',
            'shortDescription' => 'Test card payment',
            'checksum'         => $cs,
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => null,
            'redirectUrl'      => REDIRECT_URL,
        ],
    ], ['cardPayment' => 'true', 'rememberMe' => 'false']);

    echo "  → Card payment returns HTML or a redirect URL, not JSON.\n";
    echo "  → If you get a URL back, open it in a browser to complete 3DS.\n\n";
}

function testChecksum(): void
{
    echo "━━━ TEST: Verify Checksum via CPay endpoint ━━━\n\n";
    echo "  This uses CPay's own /getchecksum endpoint to verify our calculation.\n\n";

    $id     = txnId('CHKSUM');
    $amount = TEST_AMOUNT;
    $msisdn = TEST_MSISDN;

    echo "  TxnId: {$id}\n";
    $ourChecksum = checksum($id, $amount, $msisdn);
    echo "\n";

    request('POST', '/api/cpaypayments/getchecksum', [
        'transactionRequest' => [
            'extTransactionId' => $id,
            'clientCode'       => CLIENT_CODE,
            'msisdn'           => $msisdn,
            'otp'              => '',
            'amount'           => $amount,
            'shortDescription' => '',
            'checksum'         => '',
            'currency'         => 'LSL',
            'otpMedium'        => 'sms',
            'additionalData'   => '',
            'redirectUrl'      => '',
        ],
    ]);

    echo "  → Our computed checksum:  {$ourChecksum}\n";
    echo "  → Compare to CPay response above — they must match.\n\n";
}

function testStatus(): void
{
    echo "━━━ TEST: Transaction Status Check ━━━\n\n";
    $id   = FIXED_TXN_ID;
    $date = date('Y-m-d');

    echo "  Querying txnId: {$id}\n";
    echo "  Date:           {$date}\n\n";

    request('GET', '/api/cpaypayments/transaction-status', [], [
        'requestReference' => $id,
        'dateTime'         => $date,
    ]);
}

function testDisburseExternal(): void
{
    echo "━━━ TEST: External Disbursement (MPesa) ━━━\n\n";
    $id      = txnId('DISB');
    $amount  = TEST_AMOUNT;
    $msisdn  = TEST_MSISDN;         // 8-digit in body
    $msisdn8 = TEST_MSISDN;         // same here since TEST_MSISDN is already 8-digit

    echo "  TxnId: {$id}\n";
    $cs = checksum($id, $amount, $msisdn);
    echo "\n";

    request('POST', '/api/disbursements/external-payment', [
        'transactionRequest' => [
            'transactionRequest' => [   // double-wrapped per docs
                'extTransactionId' => $id,
                'clientCode'       => CLIENT_CODE,
                'msisdn'           => $msisdn,
                'amount'           => $amount,
                'shortDescription' => 'Test disbursement',
                'checksum'         => $cs,
                'currency'         => 'LSL',
                'otp'              => '',
                'redirectUrl'      => REDIRECT_URL,
                'additionalData'   => 'loan:' . LOAN_NUMBER,
            ],
        ],
    ], ['destinationOperator' => 'mpesa', 'destinationWalletNumber' => $msisdn8]);
}

function testDisburseWallet(): void
{
    echo "━━━ TEST: Wallet Top-Up Advance ━━━\n\n";
    $id      = txnId('WALLET');
    $amount  = TEST_AMOUNT;
    $msisdn  = TEST_MSISDN;   // 8-digit in body for wallet-topup
    $msisdn8 = TEST_MSISDN;

    echo "  TxnId: {$id}\n";
    // Checksum uses +266 format per our service logic
    // If this fails, try passing $msisdn (8-digit) here instead
    $msisdnFull = '+266' . $msisdn8;
    $cs = checksum($id, $amount, $msisdnFull);
    echo "\n";

    request('POST', '/api/disbursements/wallet-topup-advance', [
        'transactionRequest' => [
            'transactionRequest' => [
                'extTransactionId' => $id,
                'clientCode'       => CLIENT_CODE,
                'msisdn'           => $msisdn,
                'amount'           => $amount,
                'shortDescription' => 'Test wallet top-up',
                'checksum'         => $cs,
                'currency'         => 'LSL',
                'redirectUrl'      => REDIRECT_URL,
                'additionalData'   => [
                    'recipientKyc' => [
                        'idDocument' => [[
                            'idType'        => 'ID',
                            'idNumber'      => 'TEST123456',
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

// ============================================================
// RUNNER
// ============================================================

match ($test) {
    'mobile'   => testMobileMoney(),
    'confirm'  => testConfirmOtp(),
    'card'     => testCard(),
    'checksum' => testChecksum(),
    'status'   => testStatus(),
    'disburse' => testDisburseExternal(),
    'wallet'   => testDisburseWallet(),
    'all'      => (function () {
        testChecksum();
        testMobileMoney();
        testStatus();
    })(),
    default => (function () use ($test) {
        echo "Unknown test: {$test}\n";
        echo "Available: mobile, confirm, card, checksum, status, disburse, wallet, all\n\n";
    })(),
};