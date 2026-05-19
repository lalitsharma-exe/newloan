<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\TreasuryTransaction;

class ReconcileHistoricalPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and reconcile all historically verified/reversed payments to the Treasury GL Ledger';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Payment Ledger Reconciliation...');

        // 1. Reconcile Verified Payments
        $verifiedPayments = Payment::where('status', 'verified')->get();
        $this->info("Found {$verifiedPayments->count()} verified payments in the database.");
        
        $verifiedCount = 0;
        foreach ($verifiedPayments as $payment) {
            $exists = TreasuryTransaction::where('payment_id', $payment->id)
                ->where('direction', 'in')
                ->exists();
                
            if (!$exists) {
                Payment::postToTreasury($payment);
                $verifiedCount++;
                $this->line("Reconciled verified payment: {$payment->payment_reference} (M{$payment->amount})");
            }
        }

        // 2. Reconcile Reversed Payments
        $reversedPayments = Payment::where('status', 'reversed')->get();
        $this->info("Found {$reversedPayments->count()} reversed payments in the database.");
        
        $reversedCount = 0;
        foreach ($reversedPayments as $payment) {
            $exists = TreasuryTransaction::where('payment_id', $payment->id)
                ->where('type', 'adjustment')
                ->exists();
                
            if (!$exists) {
                Payment::postReversalToTreasury($payment);
                $reversedCount++;
                $this->line("Reconciled reversed payment (adjustment): {$payment->payment_reference} (M{$payment->amount})");
            }
        }

        $this->info('────────────────────────────────────────');
        $this->info("✓ Success! Reconciled {$verifiedCount} verified payments and {$reversedCount} reversals to the Treasury GL.");
        return Command::SUCCESS;
    }
}
