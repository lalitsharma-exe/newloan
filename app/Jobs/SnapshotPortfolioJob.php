<?php

namespace App\Jobs;

use App\Models\FinancialPeriod;
use App\Models\FsProvision;
use App\Models\Loan;
use App\Models\PortfolioSnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SnapshotPortfolioJob implements ShouldQueue
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
        $start = $targetDate->copy()->startOfMonth()->toDateString();
        $end = $targetDate->copy()->endOfMonth()->toDateString();

        // 2. Active Loan counts and gross outstanding book size
        $activeLoans = Loan::whereIn('status', ['active', 'overdue', 'arrears'])->get();
        $activeLoanCount = $activeLoans->count();
        $grossLoanBook = (double) $activeLoans->sum('outstanding_balance');

        // 3. Disbursements in month
        $disbursements = Loan::whereBetween('disbursement_date', [$start, $end])->get();
        $disbursedCount = $disbursements->count();
        $disbursedAmount = (double) $disbursements->sum('principal_amount');

        // 4. Settled in month (loans whose closed_at falls inside that calendar month and status is closed or paid_off)
        $settledLoans = Loan::whereBetween('closed_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->whereIn('status', ['paid_off', 'closed'])
            ->get();
        $settledCount = $settledLoans->count();
        $settledAmount = (double) $settledLoans->sum('principal_amount');

        // 5. Written off in month
        $writtenOff = Loan::whereBetween('closed_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->where('status', 'written_off')
            ->get();
        $writtenOffCount = $writtenOff->count();
        $writtenOffAmount = (double) $writtenOff->sum('outstanding_balance');

        // 6. Portfolio at Risk (PAR-30 & PAR-90) calculation
        $par30Amount = 0.00;
        $par90Amount = 0.00;

        foreach ($activeLoans as $loan) {
            $daysOverdue = $loan->days_overdue;
            if ($daysOverdue >= 30) {
                $par30Amount += $loan->outstanding_balance;
            }
            if ($daysOverdue >= 90) {
                $par90Amount += $loan->outstanding_balance;
            }
        }

        $par30Rate = $grossLoanBook > 0 ? ($par30Amount / $grossLoanBook) : 0.00;
        $par90Rate = $grossLoanBook > 0 ? ($par90Amount / $grossLoanBook) : 0.00;

        // 7. Retrieve provision balance from fs_provisions for that period
        $periodLabel = sprintf("%04d-%02d", $year, $month);
        $period = FinancialPeriod::where('period_label', $periodLabel)->first();
        $provisionBalance = 0.00;
        if ($period) {
            $provisionBalance = (double) FsProvision::where('period_id', $period->id)
                ->where('provision_type', 'bad_debt')
                ->value('amount') ?? 0.00;
        }

        // 8. Create or Update Snapshot
        PortfolioSnapshot::updateOrCreate(
            ['snapshot_date' => $end],
            [
                'active_loan_count'  => $activeLoanCount,
                'gross_loan_book'    => $grossLoanBook,
                'disbursed_count'    => $disbursedCount,
                'disbursed_amount'   => $disbursedAmount,
                'settled_count'      => $settledCount,
                'settled_amount'     => $settledAmount,
                'written_off_count'  => $writtenOffCount,
                'written_off_amount' => $writtenOffAmount,
                'par_30_amount'      => $par30Amount,
                'par_30_rate'        => $par30Rate,
                'par_90_amount'      => $par90Amount,
                'par_90_rate'        => $par90Rate,
                'provision_balance'  => $provisionBalance,
            ]
        );
    }
}
