<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Referral;
use App\Models\LoanInstallment;
use Carbon\Carbon;

class FixReferralsCommand extends Command
{
    protected $signature = 'loans:fix-referrals';
    protected $description = 'Retroactively qualify referrals where the referred user has paid their first installment';

    public function handle()
    {
        $this->info('Starting referral fix process...');

        // 1. Get all referrals that are NOT yet qualified/paid
        $referrals = Referral::whereNotIn('status', ['qualified', 'payout_pending', 'paid'])->get();

        $count = 0;
        foreach ($referrals as $ref) {
            // 2. Find any loan of the referred user where installment #1 is paid
            // Or just any installment is paid (since paying any installment means they are active)
            $paidInstallment = LoanInstallment::whereHas('loan', function($q) use ($ref) {
                $q->where('user_id', $ref->referred_id);
            })
            ->where('installment_number', 1)
            ->where('status', 'paid')
            ->first();

            if ($paidInstallment) {
                $this->info("Qualifying referral for Referrer ID: {$ref->referrer_id} (Referred: {$ref->referred_id})");
                
                $ref->update([
                    'status'       => 'qualified',
                    'amount'       => 50,
                    'qualified_at' => $paidInstallment->paid_at ?? Carbon::now(),
                    'loan_id'      => $paidInstallment->loan_id,
                ]);
                
                $count++;
            }
        }

        $this->info("Process complete. {$count} referrals retroactively qualified.");
    }
}
