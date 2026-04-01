<?php

use App\Models\Loan;
use App\Models\User;
use App\Services\CPayService;

$cpay = app(CPayService::class);
// Create a fake loan using the first user
$user = User::first();
$loan = new Loan([
    'loan_number' => 'LN-' . rand(1000, 9999),
    'principal_amount' => 50.00,
    'term_months' => 1,
]);
$loan->user_id = $user->id;

// Test disburseToWallet
$result = $cpay->disburseToWallet($loan, '58145851', 'DISB-' . time());
echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;

// Test disburseExternal
$result2 = $cpay->disburseExternal($loan, '58145851', 'VODACOM', 'DISB-' . time());
echo json_encode($result2, JSON_PRETTY_PRINT) . PHP_EOL;
