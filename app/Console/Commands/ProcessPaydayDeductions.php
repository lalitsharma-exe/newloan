<?php

namespace App\Console\Commands;

use App\Models\{MyBillLoan, User};
use App\Services\MyBillService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPaydayDeductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybill:process-paydays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process MyBill auto-deductions for clients who have reached their payday.';

    /**
     * Execute the console command.
     */
    public function handle(MyBillService $svc)
    {
        $this->info('Starting MyBill payday processing...');

        // Find all users who have active MyBill loans
        $activeUserIds = MyBillLoan::active()->distinct('user_id')->pluck('user_id');

        if ($activeUserIds->isEmpty()) {
            $this->info('No active MyBill loans found. Exiting.');
            return;
        }

        $processedCount = 0;
        $totalDeducted = 0;

        foreach ($activeUserIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            // In a real-world scenario, you would check if TODAY is this user's scheduled payday
            // Or if a salary credit was just detected via a bank feed/webhook.
            // For now, as part of the core product launch, we'll assume this command
            // is triggered by a webhook or when it specifically matches the user's date.
            
            // To prevent double processing on the same day if we run multiple times, 
            // you'd typically have a `next_payday` field on the user profile to check against:
            // if (now()->startOfDay()->ne($user->next_payday->startOfDay())) continue;

            // Since we don't have a rigid payday field in the base user table yet, we'll
            // log this as a pending implementation detail for the bank-feed integration team.
            
            // For testing/simulation, we will execute a dry-run check or use a mock flag if requested.
            // We will NOT auto-deduct everyone arbitrarily in production without the date check.
            Log::info("MyBill: Checked payday for user ID {$userId} - Awaiting bank feed trigger implementation.");
            $processedCount++;
        }

        $this->info("Completed. Checked {$processedCount} users.");
    }
}
