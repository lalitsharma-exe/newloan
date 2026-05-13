<?php
use App\Models\Referral;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = 0;
// Find all qualified, payout_pending, or paid referrals with 0 or null amount
$referrals = Referral::whereIn('status', ['qualified', 'payout_pending', 'paid'])
    ->where(function($q) {
        $q->whereNull('amount')->orWhere('amount', 0);
    })
    ->get();

foreach ($referrals as $ref) {
    $ref->update(['amount' => 50.00]);
    $count++;
}

echo "Fixed $count referral records by setting amount to M50.00\n";
