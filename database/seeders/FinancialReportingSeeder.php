<?php

namespace Database\Seeders;

use App\Models\FinancialPeriod;
use App\Models\FsRevenueLine;
use App\Models\FsExpenseLine;
use App\Models\FsPpeRegister;
use App\Models\FsProvision;
use App\Models\FsEquityMovement;
use App\Models\PortfolioSnapshot;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FinancialReportingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Monthly periods for 2026 (Jan to Dec)
        $periodsMap = [];
        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create(2026, $m, 1);
            $label = sprintf("2026-%02d", $m);
            $period = FinancialPeriod::updateOrCreate(
                ['period_label' => $label],
                [
                    'period_end_date' => $date->copy()->endOfMonth()->toDateString(),
                    'year'            => 2026,
                    'month'           => $m,
                    'status'          => $m <= 4 ? 'closed' : 'open', // lock past periods
                ]
            );
            $periodsMap[$m] = $period->id;

            // Seed monthly revenues
            FsRevenueLine::updateOrCreate(
                ['period_id' => $period->id, 'revenue_type' => 'interest_received'],
                ['amount' => 85000 + ($m * 4200)]
            );
            FsRevenueLine::updateOrCreate(
                ['period_id' => $period->id, 'revenue_type' => 'initiation_fees'],
                ['amount' => 12000 + ($m * 1200)]
            );
            FsRevenueLine::updateOrCreate(
                ['period_id' => $period->id, 'revenue_type' => 'admin_fees'],
                ['amount' => 4500 + ($m * 300)]
            );
            FsRevenueLine::updateOrCreate(
                ['period_id' => $period->id, 'revenue_type' => 'penalties'],
                ['amount' => 1500 + ($m * 200)]
            );

            // Seed monthly expenses
            FsExpenseLine::updateOrCreate(
                ['period_id' => $period->id, 'expense_code' => 'EXP-SAL'],
                ['expense_label' => 'Staff Salaries', 'amount' => 32000]
            );
            FsExpenseLine::updateOrCreate(
                ['period_id' => $period->id, 'expense_code' => 'EXP-RENT'],
                ['expense_label' => 'Office Rent', 'amount' => 8500]
            );
            FsExpenseLine::updateOrCreate(
                ['period_id' => $period->id, 'expense_code' => 'EXP-MKT'],
                ['expense_label' => 'Marketing Campaigns', 'amount' => 3500 + ($m * 200)]
            );

            // Seed monthly provisions
            FsProvision::updateOrCreate(
                ['period_id' => $period->id, 'provision_type' => 'bad_debt'],
                ['amount' => 12000 + ($m * 1100)]
            );

            // Seed Monthly Portfolio Snapshots
            PortfolioSnapshot::updateOrCreate(
                ['snapshot_date' => $period->period_end_date->toDateString()],
                [
                    'active_loan_count'  => 140 + ($m * 12),
                    'gross_loan_book'    => 1200000 + ($m * 95000),
                    'disbursed_count'    => 18 + ($m % 3),
                    'disbursed_amount'   => 150000 + ($m * 10000),
                    'settled_count'      => 8 + ($m % 2),
                    'settled_amount'     => 75000 + ($m * 4000),
                    'written_off_count'  => $m % 4 === 0 ? 1 : 0,
                    'written_off_amount' => $m % 4 === 0 ? 8500.00 : 0.00,
                    'par_30_amount'      => 45000 + ($m * 2500),
                    'par_30_rate'        => 0.045 - ($m * 0.001),
                    'par_90_amount'      => 12000 + ($m * 800),
                    'par_90_rate'        => 0.012 + ($m * 0.0002),
                    'provision_balance'  => 12000 + ($m * 1100),
                ]
            );
        }

        // 2. Seed Quarterly periods for 2026 (Q1 & Q2)
        for ($q = 1; $q <= 2; $q++) {
            FinancialPeriod::updateOrCreate(
                ['period_label' => "2026-Q{$q}"],
                [
                    'period_end_date' => Carbon::create(2026, $q * 3, 1)->endOfMonth()->toDateString(),
                    'year'            => 2026,
                    'quarter'         => $q,
                    'status'          => $q == 1 ? 'closed' : 'open',
                ]
            );
        }

        // 3. Seed Annual period for 2026
        FinancialPeriod::updateOrCreate(
            ['period_label' => '2026-annual'],
            [
                'period_end_date' => '2026-12-31',
                'year'            => 2026,
                'status'          => 'open',
            ]
        );

        // 4. Seed active PPE Register Assets
        FsPpeRegister::updateOrCreate(
            ['asset_name' => 'HQ Dell Servers & Rack Systems'],
            [
                'asset_class'       => 'Computer Hardware',
                'purchase_date'     => '2026-01-10',
                'cost'              => 85000.00,
                'useful_life_years' => 5,
                'accumulated_dep'   => 5666.67,
                'net_book_value'    => 79333.33,
                'status'            => 'active',
            ]
        );

        FsPpeRegister::updateOrCreate(
            ['asset_name' => 'Toyota Hilux double cab'],
            [
                'asset_class'       => 'Motor Vehicles',
                'purchase_date'     => '2026-02-15',
                'cost'              => 380000.00,
                'useful_life_years' => 7,
                'accumulated_dep'   => 18095.24,
                'net_book_value'    => 361904.76,
                'status'            => 'active',
            ]
        );

        // 5. Seed Consolidated Shareholder Equity Movements
        $janPeriodId = $periodsMap[1];
        FsEquityMovement::updateOrCreate(
            ['period_id' => $janPeriodId, 'movement_type' => 'capital_injection'],
            ['amount' => 500000.00]
        );
        FsEquityMovement::updateOrCreate(
            ['period_id' => $janPeriodId, 'movement_type' => 'retained_earnings'],
            ['amount' => 1250000.00]
        );
    }
}
