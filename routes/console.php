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
