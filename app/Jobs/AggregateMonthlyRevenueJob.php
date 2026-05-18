<?php

namespace App\Jobs;

use App\Models\FinancialPeriod;
use App\Models\FsRevenueLine;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AggregateMonthlyRevenueJob implements ShouldQueue
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
        // 1. Resolve target period (default to prior calendar month)
        if ($this->targetYear && $this->targetMonth) {
            $targetDate = Carbon::create($this->targetYear, $this->targetMonth, 1);
        } else {
            $targetDate = Carbon::now()->subMonth();
        }

        $year = $targetDate->year;
        $month = $targetDate->month;
        $periodLabel = sprintf("%04d-%02d", $year, $month);

        // 2. Ensure Financial Period exists
        $period = FinancialPeriod::updateOrCreate(
            ['period_label' => $periodLabel],
            [
                'period_end_date' => $targetDate->copy()->endOfMonth()->toDateString(),
                'year'            => $year,
                'month'           => $month,
                'status'          => 'open',
            ]
        );

        // If the period is locked (closed or audited), we skip modification to preserve historical integrity
        if ($period->status !== 'open') {
            return;
        }

        // 3. Aggregate all verified payments received in that calendar month
        $start = $targetDate->copy()->startOfMonth()->toDateTimeString();
        $end = $targetDate->copy()->endOfMonth()->toDateTimeString();

        $totals = Payment::where('status', 'verified')
            ->whereBetween('verified_at', [$start, $end])
            ->selectRaw('
                SUM(principal_portion) as capital,
                SUM(interest_portion) as interest,
                SUM(initiation_fee_portion) as initiation,
                SUM(admin_fee_portion) as admin,
                SUM(penalty_portion) as penalty
            ')
            ->first();

        // 4. Map and store revenue categories
        $revenueMap = [
            'interest_received' => $totals->interest ?? 0.00,
            'initiation_fees'   => $totals->initiation ?? 0.00,
            'admin_fees'        => $totals->admin ?? 0.00,
            'penalties'         => $totals->penalty ?? 0.00,
        ];

        foreach ($revenueMap as $type => $amount) {
            FsRevenueLine::updateOrCreate(
                [
                    'period_id'    => $period->id,
                    'revenue_type' => $type,
                ],
                ['amount' => (double) $amount]
            );
        }
    }
}
