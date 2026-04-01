<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CPay — Chaperone Payments (Lesotho)
    |--------------------------------------------------------------------------
    | UAT:  https://cpay-uat-env.chaperone.co.ls:5100
    | Docs: /chaperone/api/v1.1/cpayments.json
    |
    | These are fallback values. Production values are stored in SystemSettings
    | (admin settings panel) and override these at runtime via CPayService.
    |--------------------------------------------------------------------------
    */
    'cpay' => [
        'client_code' => env('CPAY_CLIENT_CODE', ''),
        'api_key'     => env('CPAY_API_KEY', ''),
        'secret_key'  => env('CPAY_SECRET_KEY', ''),
        'sandbox'     => env('CPAY_SANDBOX', true),
        'sandbox_url' => 'https://cpay-uat-env.chaperone.co.ls:5100',
        'live_url'    => env('CPAY_LIVE_URL', 'https://api.chaperone.co.ls'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BulkSMS South Africa — SMS Gateway
    |--------------------------------------------------------------------------
    | Used for phone number verification OTPs at registration.
    | Get credentials at: https://bulksmssouthafrica.co.za
    |--------------------------------------------------------------------------
    */
    'bulksms' => [
        'username' => env('BULKSMS_USERNAME', ''),
        'password' => env('BULKSMS_PASSWORD', ''),
        'sender'   => env('BULKSMS_SENDER', 'MyLoan'),
    ],

];
