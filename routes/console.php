<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run daily: marks overdue installments + applies M20/10-day penalty
Schedule::command('loans:apply-penalties')->dailyAt('01:00')->appendOutputTo(storage_path('logs/cron.log'));

// Send payment reminders 2 days before payday
Schedule::command('loans:send-reminders')->dailyAt('09:00')->appendOutputTo(storage_path('logs/cron.log'));

// Process MyBill payday auto-deductions
Schedule::command('mybill:process-paydays')->dailyAt('06:00')->appendOutputTo(storage_path('logs/cron.log'));

// Expire old inactive applications
Schedule::command('loans:expire-applications')->dailyAt('02:00')->appendOutputTo(storage_path('logs/cron.log'));

// Capture daily liquidity snapshot & refresh forecasts
Schedule::command('financial:refresh')->dailyAt('23:30')->appendOutputTo(storage_path('logs/cron.log'));

// ── MYLOAN FLOAT AUTOMATION ──────────────────────────────────
// On the 20th: Transition disbursed floats to DUE
Schedule::command('float:transition-due')->monthlyOn(20, '08:00')->appendOutputTo(storage_path('logs/cron.log'));

// On the 1st: Apply penalties to overdue floats
Schedule::command('float:penalties')->monthlyOn(1, '00:01')->appendOutputTo(storage_path('logs/cron.log'));

// ── DIRECTOR INVESTMENT AUTOMATION ────────────────────────────
// On the 1st: Accrue 5% flat interest on MD investments
Schedule::command('md:accrue-interest')->monthlyOn(1, '00:05')->appendOutputTo(storage_path('logs/cron.log'));

// ── FINANCIAL REPORTING AUTOMATION ────────────────────────────
// Monthly: On the 1st of each month aggregate revenue, compute provisions, depreciation, and snapshots
Schedule::job(new \App\Jobs\AggregateMonthlyRevenueJob())->monthlyOn(1, '01:00');
Schedule::job(new \App\Jobs\ComputeProvisionJob())->monthlyOn(1, '01:30');
Schedule::job(new \App\Jobs\RunDepreciationJob())->monthlyOn(1, '02:00');
Schedule::job(new \App\Jobs\SnapshotPortfolioJob())->monthlyOn(1, '02:30');
Schedule::job(new \App\Jobs\SendMonthlyReportNotificationJob())->monthlyOn(1, '06:00');

// Daily: Assets vs Liabilities + Equity reconciliation check
Schedule::job(new \App\Jobs\BalanceCheckAlertJob())->dailyAt('06:00');

// Quarterly: Generate Q1, Q2, Q3, Q4 aggregates on the 1st day of next quarter
Schedule::job(new \App\Jobs\GenerateQuarterlyReportJob(1))->yearlyOn(4, 1, '03:00');
Schedule::job(new \App\Jobs\GenerateQuarterlyReportJob(2))->yearlyOn(7, 1, '03:00');
Schedule::job(new \App\Jobs\GenerateQuarterlyReportJob(3))->yearlyOn(10, 1, '03:00');
Schedule::job(new \App\Jobs\GenerateQuarterlyReportJob(4))->yearlyOn(1, 1, '03:00');

// Annual: Consolidate annual statutory accounts on 1st January
Schedule::job(new \App\Jobs\PrepareAnnualStatementJob())->yearlyOn(1, 1, '04:00');
