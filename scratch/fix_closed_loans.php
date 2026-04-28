<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Loan;

// Fix LN-00015 and any other 'closed' loans that still have a balance
$closedLoans = Loan::where('status', 'closed')->where('outstanding_balance', '>', 0)->get();

foreach ($closedLoans as $loan) {
    echo "Fixing Loan: {$loan->loan_number}\n";
    
    // 1. Zero out balance
    $loan->update(['outstanding_balance' => 0]);
    
    // 2. Waive installments
    $loan->installments()
        ->whereNotIn('status', ['paid', 'waived'])
        ->update([
            'status' => 'waived',
            'outstanding_amount' => 0,
            'notes' => "Manually fixed: Account was closed but balance remained."
        ]);
        
    echo "Fixed.\n";
}

echo "Done.\n";
