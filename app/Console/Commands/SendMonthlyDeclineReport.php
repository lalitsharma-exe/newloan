<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeclineRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendMonthlyDeclineReport extends Command
{
    protected $signature = 'report:decline-monthly';
    protected $description = 'Send monthly decline reason summary report to Credit Manager';

    public function handle()
    {
        $start = now()->subMonth()->startOfMonth();
        $end = now()->subMonth()->endOfMonth();

        $records = DeclineRecord::whereBetween('declined_at', [$start, $end])
            ->with('category')
            ->get();

        if ($records->isEmpty()) {
            $this->info('No declines for the previous month.');
            return;
        }

        $totalCount = $records->count();
        $totalValue = $records->sum('loan_amount');

        $breakdown = $records->groupBy('category_id')->map(function ($group) use ($totalCount) {
            return [
                'name' => $group->first()->category->name,
                'count' => $group->count(),
                'percentage' => round(($group->count() / $totalCount) * 100, 1),
                'value' => $group->sum('loan_amount')
            ];
        })->sortByDesc('count');

        // Prepare CSV
        $csvHeader = ['Date', 'App Ref', 'Applicant', 'Category', 'Reason', 'Amount', 'Notes'];
        $csvData = [];
        foreach ($records as $r) {
            $csvData[] = [
                $r->declined_at->format('Y-m-d'),
                $r->application_number,
                $r->applicant_name,
                $r->category->name,
                $r->reason,
                $r->loan_amount,
                $r->notes
            ];
        }

        // In a real scenario, you would use a Mailable class
        // Mail::to('credit-manager@myloan.com')->send(new MonthlyDeclineReport($breakdown, $csvData));

        $this->info("Monthly decline report generated: {$totalCount} declines, total value M{$totalValue}");
    }
}
