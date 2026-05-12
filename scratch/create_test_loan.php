<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\LoanProduct;
use App\Models\LoanApplication;
use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Support\Str;

$user = User::where('role', 'borrower')->first();
$product = LoanProduct::find(1);

if (!$user || !$product) {
    die("User or Product not found\n");
}

try {
    \Illuminate\Support\Facades\DB::transaction(function() use ($user, $product) {
        $amount = 5000;
        $term = 6;
        $rate = $product->interest_rate; // 15%
        
        // 1. Create Application
        $app = LoanApplication::create([
            'user_id' => $user->id,
            'loan_product_id' => $product->id,
            'application_number' => 'APP-' . strtoupper(Str::random(8)),
            'status' => 'disbursed',
            'first_name' => $user->name,
            'surname' => 'Test',
            'national_id' => '6543210987',
            'requested_amount' => $amount,
            'requested_term' => $term,
            'approved_amount' => $amount,
            'approved_term' => $term,
            'approved_interest_rate' => $rate,
            'disbursement_date' => now(),
            'submitted_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(3),
            'decided_at' => now()->subDays(1),
        ]);

        // Calculate totals for Loan
        $totalInterest = round($amount * ($rate / 100) * $term, 2);
        $totalInitiation = round($amount * (($product->initiation_fee_rate ?? 40) / 100), 2);
        $totalAdmin = (float)($product->admin_fee_fixed ?? 50) * $term;
        $totalAmount = $amount + $totalInterest + $totalInitiation + $totalAdmin;
        $monthly = round($totalAmount / $term, 2);

        // 2. Create Loan
        $loan = Loan::create([
            'loan_number' => 'LN-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'application_id' => $app->id,
            'loan_product_id' => $product->id,
            'principal_amount' => $amount,
            'interest_rate' => $rate,
            'term_months' => $term,
            'total_amount' => $totalAmount,
            'outstanding_balance' => $totalAmount,
            'monthly_installment' => $monthly,
            'status' => 'active',
            'disbursement_date' => now(),
            'maturity_date' => now()->addMonths($term)->day(25),
            'first_payment_date' => now()->addMonth()->day(25),
        ]);

        // 3. Create Installments
        for ($i = 1; $i <= $term; $i++) {
            $interest = round($amount * ($rate / 100), 2);
            $prinInstall = ($i == $term) ? ($amount - (round($amount / $term, 2) * ($term - 1))) : round($amount / $term, 2);
            $admin = (float)($product->admin_fee_fixed ?? 50);
            $initiation = ($i == 1) ? $totalInitiation : 0;
            
            $totalInstall = $prinInstall + $interest + $admin + $initiation;

            LoanInstallment::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => now()->addMonths($i)->day(25),
                'principal_amount' => $prinInstall,
                'interest_amount' => $interest,
                'admin_fee' => $admin, // Note: admin_fee might not be in migration but total_amount includes it
                'initiation_fee' => $initiation,
                'total_amount' => $totalInstall,
                'outstanding_amount' => $totalInstall,
                'status' => 'pending',
            ]);
        }
        
        echo "Test Loan created: {$loan->loan_number} for M" . number_format($totalAmount, 2) . "\n";
    });
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
