<?php

namespace App\Modules\FinancialStatements\Services;

use App\Models\FinancialPeriod;
use App\Models\FsRevenueLine;
use App\Models\FsExpenseLine;
use App\Models\FsProvision;
use App\Models\FsPpeRegister;
use App\Models\FsEquityMovement;
use App\Models\TreasuryAccount;

class AnnualConsolidationService
{
    /**
     * Consolidate twelve months of operational data into the annual audited statements.
     */
    public function consolidate(int $year): array
    {
        // 1. Get all monthly period IDs for the target year
        $periodIds = FinancialPeriod::where('year', $year)
            ->whereNotNull('month')
            ->whereNull('quarter')
            ->pluck('id');

        // 2. Revenue — sum all monthly revenue categories
        $revenue = FsRevenueLine::whereIn('period_id', $periodIds)
            ->selectRaw('revenue_type, SUM(amount) as annual_amount')
            ->groupBy('revenue_type')
            ->pluck('annual_amount', 'revenue_type')
            ->toArray();

        // 3. Expenses — sum all monthly expense entries
        $expenses = FsExpenseLine::whereIn('period_id', $periodIds)
            ->selectRaw('expense_code, expense_label, SUM(amount) as annual_amount')
            ->groupBy('expense_code', 'expense_label')
            ->get()
            ->toArray();

        // 4. Provisions — Closing balance of bad debts (typically December snapshot)
        $decPeriodId = FinancialPeriod::where('period_label', $year . '-12')->value('id');
        $provisionsQuery = FsProvision::query();
        if ($decPeriodId) {
            $provisionsQuery->where('period_id', $decPeriodId);
        } else {
            $provisionsQuery->whereIn('period_id', $periodIds);
        }
        $provisions = $provisionsQuery->selectRaw('provision_type, SUM(amount) as amount')
            ->groupBy('provision_type')
            ->get()
            ->toArray();

        // 5. Property, Plant, & Equipment (PPE) Net Book Value closing balances
        $ppe = FsPpeRegister::where('status', 'active')
            ->selectRaw('asset_class, SUM(cost) as cost, SUM(accumulated_dep) as accumulated_dep, SUM(net_book_value) as net_book_value')
            ->groupBy('asset_class')
            ->get()
            ->toArray();

        // 6. Equity & Reserves closing balances
        $equityMovements = FsEquityMovement::selectRaw('movement_type, SUM(amount) as amount')
            ->groupBy('movement_type')
            ->pluck('amount', 'movement_type')
            ->toArray();

        // 7. Cash closing balances (reconciled treasury accounts)
        $cashBalance = (float) TreasuryAccount::where('is_active', true)->sum('balance');

        // 8. Create or update the Consolidated Annual Period
        $annualPeriod = FinancialPeriod::updateOrCreate(
            ['period_label' => $year . '-annual'],
            [
                'period_end_date' => $year . '-12-31',
                'year'            => $year,
                'status'          => 'closed',
            ]
        );

        // Write consolidated aggregates to the annual period table
        foreach ($revenue as $type => $amount) {
            FsRevenueLine::updateOrCreate(
                [
                    'period_id'    => $annualPeriod->id,
                    'revenue_type' => $type,
                ],
                ['amount' => $amount]
            );
        }

        foreach ($expenses as $exp) {
            FsExpenseLine::updateOrCreate(
                [
                    'period_id'    => $annualPeriod->id,
                    'expense_code' => $exp['expense_code'],
                ],
                [
                    'expense_label' => $exp['expense_label'],
                    'amount'        => $exp['annual_amount'],
                ]
            );
        }

        return compact('revenue', 'expenses', 'provisions', 'ppe', 'equityMovements', 'cashBalance', 'annualPeriod');
    }
}
