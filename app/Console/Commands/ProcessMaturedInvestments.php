<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;

class ProcessMaturedInvestments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:mature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan active investments that have reached their maturity date and transition them to matured status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Investment Maturity Scan: ' . now()->toDateTimeString());

        $today = now()->toDateString();

        $investments = Investment::where('status', 'active')
            ->where('maturity_date', '<=', $today)
            ->with('investor')
            ->get();

        if ($investments->isEmpty()) {
            $this->info('No investments maturing today.');
            return;
        }

        $count = 0;
        foreach ($investments as $inv) {
            $inv->update([
                'status' => 'matured',
            ]);

            $this->line("Investment #{$inv->contract_ref} (Investor: {$inv->investor->full_name}) has matured on {$inv->maturity_date}");
            $count++;
        }

        $this->info("Successfully marked {$count} investments as matured.");
    }
}
