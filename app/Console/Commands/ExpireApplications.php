<?php

namespace App\Console\Commands;

use App\Models\LoanApplication;
use Illuminate\Console\Command;

class ExpireApplications extends Command
{
    protected $signature = 'loans:expire-applications';
    protected $description = 'Expire draft or submitted applications older than 30 days';

    public function handle(): void
    {
        $days = 30;
        $expiryDate = now()->subDays($days);

        $expiredCount = LoanApplication::whereIn('status', ['draft', 'submitted', 'info_requested', 'on_hold'])
            ->where('updated_at', '<', $expiryDate)
            ->count();

        if ($expiredCount > 0) {
            LoanApplication::whereIn('status', ['draft', 'submitted', 'info_requested', 'on_hold'])
                ->where('updated_at', '<', $expiryDate)
                ->update(['status' => 'declined', 'decline_reason' => 'Application expired due to inactivity (30+ days).']);
            
            $this->info("✓ Expired {$expiredCount} applications.");
        } else {
            $this->info("No applications to expire.");
        }
    }
}
