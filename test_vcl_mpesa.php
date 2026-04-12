<?php

/**
 * COMPREHENSIVE TEST SCRIPT: Vodacom Lesotho M-Pesa Open API
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

$apiKey    = config('services.mpesa.api_key');
$publicKey = config('services.mpesa.public_key');
$host      = config('services.mpesa.host');
$market    = config('services.mpesa.market');
$env       = config('services.mpesa.env');

// Testing Parameters
$shortCode = "000000"; 
$testPhone = "000000000001"; // SUCCESS scenario number from docs
$amount    = "10";

echo "--- Vodacom Lesotho M-Pesa Full Test ---\n";
echo "Host: $host\n";
echo "Market: $market\n";

function encryptValue($value, $pubKeyBase64) {
    $pubKeyPem = "-----BEGIN PUBLIC KEY-----\n" . 
                 wordwrap($pubKeyBase64, 64, "\n", true) . 
                 "\n-----END PUBLIC KEY-----";
    $encrypted = '';
    openssl_public_encrypt($value, $encrypted, $pubKeyPem, OPENSSL_PKCS1_PADDING);
    return base64_encode($encrypted);
}

try {
    // 1. GET SESSION
    echo "1. Getting Session ID...\n";
    $encryptedApiKey = encryptValue($apiKey, $publicKey);
    $sessionUrl = "https://{$host}/{$env}/ipg/v2/{$market}/getSession/";
    
    $sessionResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $encryptedApiKey,
        'Content-Type'  => 'application/json',
        'Origin'        => '*',
    ])->get($sessionUrl);

    if ($sessionResponse->failed()) {
        die("FAILED: getSession returned " . $sessionResponse->status() . " - " . $sessionResponse->body() . "\n");
    }

    $sessionId = $sessionResponse->json()['output_SessionID'];
    echo "SUCCESS: Session ID is $sessionId\n\n";

    // 2. ENCRYPT SESSION ID & WAIT
    echo "2. Encrypting Session ID and waiting 15 seconds for propagation...\n";
    $encryptedSessionId = encryptValue($sessionId, $publicKey);
    sleep(15); 

    // 3. TEST C2B (Payment/Repayment)
    echo "3. Testing C2B Payment (Single Stage)...\n";
    $c2bUrl = "https://{$host}/{$env}/ipg/v2/{$market}/c2bPayment/singleStage/";
    $c2bPayload = [
        'input_Amount' => $amount,
        'input_Country' => 'LES',
        'input_Currency' => 'LSL',
        'input_CustomerMSISDN' => $testPhone,
        'input_ServiceProviderCode' => $shortCode,
        'input_ThirdPartyConversationID' => 'test_c2b_' . uniqid(),
        'input_TransactionReference' => 'T'.time(),
        'input_PurchasedItemsDesc' => 'Test Repayment'
    ];

    $c2bResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $encryptedSessionId,
        'Content-Type'  => 'application/json',
        'Origin'        => '*',
    ])->post($c2bUrl, $c2bPayload);

    echo "C2B Status: " . $c2bResponse->status() . "\n";
    echo "C2B Response: " . $c2bResponse->body() . "\n\n";

    // 4. TEST B2C (Disbursement)
    echo "4. Testing B2C Disbursement...\n";
    $b2cUrl = "https://{$host}/{$env}/ipg/v2/{$market}/b2cPayment/";
    $b2cPayload = [
        'input_Amount' => $amount,
        'input_Country' => 'LES',
        'input_Currency' => 'LSL',
        'input_CustomerMSISDN' => $testPhone,
        'input_ServiceProviderCode' => $shortCode,
        'input_ThirdPartyConversationID' => 'test_b2c_' . uniqid(),
        'input_TransactionReference' => 'D'.time(),
        'input_PaymentItemsDesc' => 'Test Disbursement'
    ];

    $b2cResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $encryptedSessionId,
        'Content-Type'  => 'application/json',
        'Origin'        => '*',
    ])->post($b2cUrl, $b2cPayload);

    echo "B2C Status: " . $b2cResponse->status() . "\n";
    echo "B2C Response: " . $b2cResponse->body() . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
