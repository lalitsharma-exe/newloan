<?php
/**
 * Quick test: MyMobileAPI REST SMS
 * Usage: php sms_test.php +26654567665
 */

$clientId  = '0d4576e0-658d-4bef-9cd2-eeb87d20330a';
$apiSecret = 'SahLZ110hjFZuEHc4Id/3q1dvhALSIpi';
$sender    = 'MyLoan';
$to        = $argv[1] ?? '26654567665';   // pass number as argument
$message   = 'MyLoan test OTP: 123456. Do not share.';

// Remove + and non-digits
$number = preg_replace('/[^0-9]/', '', $to);

$payload = json_encode([
    'Messages' => [[
        'Content'     => $message,
        'Destination' => $number,
        'Sender'      => $sender,
    ]],
]);

$auth = base64_encode("{$clientId}:{$apiSecret}");

$ch = curl_init('https://rest.mymobileapi.com/v1/bulkmessages');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Basic {$auth}",
    ],
]);

$body = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

echo "HTTP Status : {$http}\n";
echo "cURL Error  : " . ($err ?: 'none') . "\n";
echo "Response    : {$body}\n";

if ($http >= 200 && $http < 300) {
    echo "\n✅ SMS sent successfully!\n";
} elseif ($http === 401) {
    echo "\n❌ Invalid credentials — check ClientID and APISecret.\n";
} elseif ($http === 403) {
    echo "\n❌ Forbidden — API channel not activated. Login to mymobileapi.com → API Keys → Enable.\n";
} else {
    echo "\n❌ Failed. See response above.\n";
}
