<?php

namespace App\Jobs;

use App\Models\FinancialPeriod;
use App\Models\FsProvision;
use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ComputeProvisionJob implements ShouldQueue
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

        // 2. Fetch active/overdue/arrears loans
        $loans = Loan::whereIn('status', ['active', 'overdue', 'arrears'])->get();

        $totalProvision = 0.00;

        foreach ($loans as $loan) {
            $daysOverdue = $loan->days_overdue; // leverages class accessor for exact consistency

            if ($daysOverdue < 30) {
                $rate = 0.01;
            } elseif ($daysOverdue < 90) {
                $rate = 0.25;
            } elseif ($daysOverdue < 180) {
                $rate = 0.50;
            } elseif ($daysOverdue < 270) {
                $rate = 0.75;
            } else {
                $rate = 1.00;
            }

            $totalProvision += (float) ($loan->outstanding_balance * $rate);
        }

        // 3. Save provision record
        FsProvision::updateOrCreate(
            [
                'period_id'      => $period->id,
                'provision_type' => 'bad_debt',
            ],
            ['amount' => $totalProvision]
        );
    }
}
