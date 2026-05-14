<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Admin\FinancialService;
use App\Models\LiquiditySnapshot;

class RefreshFinancialIntelligence extends Command
{
    protected $signature = 'financial:refresh';
    protected $description = 'Refresh repayment forecasts and capture daily liquidity snapshot';

    public function handle(FinancialService $svc)
    {
        $this->info('Refreshing Repayment Forecasts...');
        $svc->refreshForecasts();

        $this->info('Capturing Liquidity Snapshot...');
        $stats = $svc->getLiquidityStats();

        LiquiditySnapshot::create([
            'snapshot_date' => now(),
            'cash_available' => $stats['cash_available'],
            'expected_inflows_30d' => $stats['expected_inflows'],
            'expected_outflows_30d' => $stats['expected_outflows'],
            'net_liquidity' => $stats['net_liquidity'],
            'runway_days' => $stats['runway_days'],
            'daily_burn_rate' => $stats['daily_burn_rate']
        ]);

        $this->success('Financial intelligence updated successfully.');
    }
}
