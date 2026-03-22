<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey    = config('services.cpay.api_key');
$clientCode = config('services.cpay.client_code');
$secretKey  = config('services.cpay.secret_key');
$baseUrl    = 'https://cpay-uat-env.chaperone.co.ls:5100';

// Try email otpMedium
$msisdn = '+26650123456';
$txnId  = 'PAY-EMAIL-' . time();
$amount = '50.00';
$salt     = $txnId . $clientCode . $amount . $msisdn;
$checksum = hash_hmac('sha256', $salt, $secretKey);

echo "=== Test 1: otpMedium=email ===\n";
$body = [
    'transactionRequest' => [
        'extTransactionId' => $txnId,
        'clientCode'       => $clientCode,
        'msisdn'           => $msisdn,
        'amount'           => $amount,
        'shortDescription' => 'Loan repayment',
        'checksum'         => $checksum,
        'currency'         => 'LSL',
        'otpMedium'        => 'email',
        'redirectUrl'      => 'https://merchant.example.com/callback',
    ]
];
$res = Illuminate\Support\Facades\Http::baseUrl($baseUrl)
    ->withHeaders(['Authorization' => $apiKey, 'Content-Type' => 'application/json'])
    ->withHeaders(['Accept' => 'application/json'])
    ->timeout(20)
    ->post('/api/cpaypayments/payment', $body);
echo "Status: " . $res->status() . "\n";
echo json_encode($res->json(), JSON_PRETTY_PRINT) . "\n\n";

// Also try the email query param
echo "=== Test 2: otpMedium=sms + email query param ===\n";
$txnId2  = 'PAY-EQRY-' . time();
$salt2     = $txnId2 . $clientCode . $amount . $msisdn;
$checksum2 = hash_hmac('sha256', $salt2, $secretKey);
$body2 = $body;
$body2['transactionRequest']['extTransactionId'] = $txnId2;
$body2['transactionRequest']['checksum'] = $checksum2;
$body2['transactionRequest']['otpMedium'] = 'sms';

$res2 = Illuminate\Support\Facades\Http::baseUrl($baseUrl)
    ->withHeaders(['Authorization' => $apiKey, 'Content-Type' => 'application/json', 'Accept' => 'application/json'])
    ->timeout(20)
    ->post('/api/cpaypayments/payment?email=test@example.com', $body2);
echo "Status: " . $res2->status() . "\n";
echo json_encode($res2->json(), JSON_PRETTY_PRINT) . "\n";
