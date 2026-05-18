<?php

namespace App\Jobs;

use App\Models\FinancialPeriod;
use App\Models\FsExpenseLine;
use App\Models\FsPpeRegister;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class RunDepreciationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $targetYear;
    protected $targetMonth;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $year = null, ?int $month = null)
    {
        $this->targetYear = $year;
        $this->targetMonth = $month;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Resolve period (default to prior calendar month)
        if ($this->targetYear && $this->targetMonth) {
            $targetDate = Carbon::create($this->targetYear, $this->targetMonth, 1);
        } else {
            $targetDate = Carbon::now()->subMonth();
        }

        $year = $targetDate->year;
        $month = $targetDate->month;
        $periodLabel = sprintf("%04d-%02d", $year, $month);

        $period = FinancialPeriod::updateOrCreate(
            ['period_label' => $periodLabel],
            [
                'period_end_date' => $targetDate->copy()->endOfMonth()->toDateString(),
                'year'            => $year,
                'month'           => $month,
                'status'          => 'open',
            ]
        );

        if ($period->status !== 'open') {
            return;
        }

        // 2. Fetch active PPE assets
        $assets = FsPpeRegister::where('status', 'active')->get();

        $monthlyDepTotal = 0.00;

        foreach ($assets as $asset) {
            // straight line: cost / (years * 12)
            $monthlyDep = $asset->cost / ($asset->useful_life_years * 12);

            $newAccDep = min($asset->cost, $asset->accumulated_dep + $monthlyDep);
            $newNetBookValue = $asset->cost - $newAccDep;

            $asset->update([
                'accumulated_dep' => $newAccDep,
                'net_book_value'  => $newNetBookValue,
                'status'          => $newAccDep >= $asset->cost ? 'disposed' : 'active',
            ]);

            $monthlyDepTotal += $monthlyDep;
        }

        // 3. Log Depreciation Expense Line
        if ($monthlyDepTotal > 0) {
            FsExpenseLine::updateOrCreate(
                [
                    'period_id'    => $period->id,
                    'expense_code' => 'EXP-DEP',
                ],
                [
                    'expense_label' => 'PPE Depreciation Expense',
                    'amount'        => $monthlyDepTotal,
                ]
            );
        }
    }
}
