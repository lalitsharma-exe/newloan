<?php

namespace App\Jobs;

use App\Models\FinancialPeriod;
use App\Models\FsExpenseLine;
use App\Models\FsRevenueLine;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class GenerateQuarterlyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $quarterNum;
    protected $year;

    /**
     * Create a new job instance.
     */
    public function __construct(int $quarterNum, ?int $year = null)
    {
        $this->quarterNum = $quarterNum;
        $this->year = $year ?: Carbon::now()->year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $periodLabel = sprintf("%04d-Q%d", $this->year, $this->quarterNum);

        // 1. Create or Resolve the Quarterly Financial Period
        $period = FinancialPeriod::updateOrCreate(
            ['period_label' => $periodLabel],
            [
                'period_end_date' => Carbon::create($this->year, $this->quarterNum * 3, 1)->endOfMonth()->toDateString(),
                'year'            => $this->year,
                'quarter'         => $this->quarterNum,
                'status'          => 'open',
            ]
        );

        if ($period->status !== 'open') {
            return;
        }

        // 2. Resolve target months for this quarter
        $startMonth = (($this->quarterNum - 1) * 3) + 1;
        $endMonth = $this->quarterNum * 3;

        $monthLabels = [];
        for ($m = $startMonth; $m <= $endMonth; $m++) {
            $monthLabels[] = sprintf("%04d-%02d", $this->year, $m);
        }

        $monthPeriods = FinancialPeriod::whereIn('period_label', $monthLabels)->pluck('id');

        // 3. Aggregate Revenue Lines
        $revenues = FsRevenueLine::whereIn('period_id', $monthPeriods)
            ->selectRaw('revenue_type, SUM(amount) as total')
            ->groupBy('revenue_type')
            ->get();

        foreach ($revenues as $rev) {
            FsRevenueLine::updateOrCreate(
                [
                    'period_id'    => $period->id,
                    'revenue_type' => $rev->revenue_type,
                ],
                ['amount' => (double) $rev->total]
            );
        }

        // 4. Aggregate Expense Lines
        $expenses = FsExpenseLine::whereIn('period_id', $monthPeriods)
            ->selectRaw('expense_code, expense_label, SUM(amount) as total')
            ->groupBy('expense_code', 'expense_label')
            ->get();

        foreach ($expenses as $exp) {
            FsExpenseLine::updateOrCreate(
                [
                    'period_id'    => $period->id,
                    'expense_code' => $exp->expense_code,
                ],
                [
                    'expense_label' => $exp->expense_label,
                    'amount'        => (double) $exp->total,
                ]
            );
        }

        // 5. Send Notification
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::send(
                $admin->id,
                'quarterly_ready',
                "Quarterly Accounts {$periodLabel} Consolidated",
                "Management accounts andCost ratios are now generated and ready for board review.",
                "/admin/financial/reports?tier=quarterly",
                "briefcase"
            );
        }
    }
}
