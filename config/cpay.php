<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CPay API Credentials
    |--------------------------------------------------------------------------
    */

    'client_code'   => env('CPAY_CLIENT_CODE'),
    'api_key'       => env('CPAY_API_KEY'),
    'secret_key'    => env('CPAY_SECRET_KEY'),
    'merchant_code' => env('CPAY_MERCHANT_CODE'),
    'sandbox'       => env('CPAY_SANDBOX', true),
    'live_url'      => env('CPAY_LIVE_URL', 'https://api.chaperone.co.ls'),

    /*
    |--------------------------------------------------------------------------
    | Card Verification via CPay
    |--------------------------------------------------------------------------
    | When true:  borrowers are redirected to the CPay payment gateway to
    |             complete an M10 card verification charge before proceeding.
    | When false: card details are saved locally (encrypted) and the borrower
    |             advances straight to the review step — no gateway redirect.
    */

    'card_verification' => env('CPAY_CARD_VERIFICATION', false),

];
