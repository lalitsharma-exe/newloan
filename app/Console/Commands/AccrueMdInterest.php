<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{DirectorInvestment, TreasuryTransaction};
use Illuminate\Support\Facades\DB;

class AccrueMdInterest extends Command
{
    protected $signature = 'md:accrue-interest';
    protected $description = 'Accrue monthly 5% interest on all active Director Investment tranches';

    public function handle()
    {
        $this->info('Starting MD Interest Accrual for ' . now()->format('F Y'));

        $investments = DirectorInvestment::where('status', 'active')->get();

        if ($investments->isEmpty()) {
            $this->info('No active investments found.');
            return;
        }

        foreach ($investments as $inv) {
            $interest = round($inv->amount_invested * $inv->monthly_rate, 2);
            
            DB::transaction(function () use ($inv, $interest) {
                $inv->increment('interest_accrued_to_date', $interest);
                
                // Note: In a full accounting system, we'd also post to a General Ledger (Dr Interest Expense / Cr Interest Payable)
                // For this LMS, we track it on the investment record itself.
            });

            $this->line("Accrued M {$interest} for Investment #{$inv->id} (Principal: M {$inv->amount_invested})");
        }

        $this->info('Accrual complete.');
    }
}
