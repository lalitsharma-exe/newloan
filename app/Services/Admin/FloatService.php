<?php

namespace App\Services\Admin;

use App\Models\FloatRecord;
use App\Models\FloatPenaltyLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FloatService
{
    /**
     * Calculate affordability for a user.
     * Formula: Disposable Income = Net Salary - Existing Deductions - Active Loan Instalments
     */
    public function calculateAffordability(User $user)
    {
        // This would typically involve looking up salary/employment data
        // For v1, we assume salary data is stored or we use a helper.
        // Assuming user has a method or relationship for salary.
        
        $netSalary = 0;
        $existingDeductions = 0;
        $activeLoanInstalments = 0;

        // Try to get latest application affordability data if available
        $latestApp = $user->loanApplications()->where('status', 'approved')->latest()->first();
        if ($latestApp && $latestApp->affordability) {
            $netSalary = $latestApp->affordability->net_salary ?? 0;
            $existingDeductions = $latestApp->affordability->total_deductions ?? 0;
        }

        $activeLoanInstalments = $user->loans()->whereIn('status', ['active', 'overdue'])->sum('monthly_installment');

        $disposable = $netSalary - $existingDeductions - $activeLoanInstalments;
        $pass = $disposable >= 625;

        return [
            'disposable_income' => $disposable,
            'result' => $pass ? 'pass' : 'fail'
        ];
    }

    /**
     * Submit a new float application.
     */
    public function submitApplication(User $user, $purpose)
    {
        // 1. Basic pre-screening
        if (!$user->is_active) throw new \Exception("Customer account is not active.");
        if (!$user->float_eligible) throw new \Exception("You are not eligible for MyLoan Float at this time.");
        if ($user->float_frozen) throw new \Exception("Your float access is suspended. Reason: " . $user->float_freeze_reason);
        
        $activeFloat = $user->activeFloat;
        if ($activeFloat) throw new \Exception("You already have an active float application or disbursement.");

        // 2. Affordability check
        $affordability = $this->calculateAffordability($user);
        if ($affordability['result'] === 'fail') {
            throw new \Exception("Insufficient disposable income to qualify for Float (Required: M625).");
        }

        // 3. Create record
        return FloatRecord::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'principal_amount' => 500,
            'charge_amount' => 125,
            'outstanding_balance' => 625,
            'purpose' => $purpose,
            'affordability_result' => $affordability['result'],
            'disposable_income' => $affordability['disposable_income'],
            'applied_at' => now(),
        ]);
    }

    /**
     * Process monthly penalties on the 1st.
     */
    public function processMonthlyPenalties()
    {
        $records = FloatRecord::whereIn('status', ['due', 'overdue'])->get();
        $count = 0;

        foreach ($records as $float) {
            DB::transaction(function () use ($float, &$count) {
                $before = $float->outstanding_balance;
                $penalty = 125.00;
                $after = $before + $penalty;

                $float->update([
                    'outstanding_balance' => $after,
                    'penalty_count' => $float->penalty_count + 1,
                    'status' => 'overdue'
                ]);

                FloatPenaltyLog::create([
                    'float_record_id' => $float->id,
                    'penalty_amount' => $penalty,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'penalty_date' => now()->toDateString(),
                ]);

                // TODO: Send SMS: "Your MyLoan Float is overdue. An additional charge of M125 has been added."
                $count++;
            });
        }

        return $count;
    }

    /**
     * Transition Disbursed to Due on the 20th.
     */
    public function transitionToDue()
    {
        $updated = FloatRecord::where('status', 'disbursed')
            ->update(['status' => 'due']);
        
        // TODO: Send SMS reminders
        return $updated;
    }
}
