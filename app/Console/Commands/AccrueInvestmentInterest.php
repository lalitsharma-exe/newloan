<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InvestmentAccrual;
use Carbon\Carbon;

class AccrueInvestmentInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:accrue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrue monthly interest on active/pending investments matching current month-end';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Investment Accrual Job: ' . now()->toDateTimeString());

        $today = now()->toDateString();

        // Find pending accruals whose date has arrived or passed
        $accruals = InvestmentAccrual::where('status', 'pending')
            ->where('accrual_date', '<=', $today)
            ->with('investment.investor')
            ->get();

        if ($accruals->isEmpty()) {
            $this->info('No pending accruals to process today.');
            return;
        }

        $count = 0;
        foreach ($accruals as $accrual) {
            $investment = $accrual->investment;
            if ($investment->status !== 'active' && $investment->status !== 'termination_pending') {
                $this->line("Skipping accrual #{$accrual->id} because parent investment status is '{$investment->status}'");
                continue;
            }

            $accrual->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            $interestLsl = number_format($accrual->interest, 2);
            $this->line("Posted LSL {$interestLsl} interest for Investment #{$investment->contract_ref} (Investor: {$investment->investor->full_name})");
            $count++;
        }

        $this->info("Successfully posted {$count} accruals.");
    }
}
