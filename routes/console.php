<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run daily: marks overdue installments + applies M20/10-day penalty
Schedule::command('loans:apply-penalties')->dailyAt('01:00');

// Send payment reminders 2 days before payday
Schedule::command('loans:send-reminders')->dailyAt('09:00');

// Process MyBill payday auto-deductions
Schedule::command('mybill:process-paydays')->dailyAt('06:00');

// Expire old inactive applications
Schedule::command('loans:expire-applications')->dailyAt('02:00');
