<?php

namespace App\Services;

use App\Models\LoanApplication;
use App\Models\Loan;
use App\Models\LoanInstallment;

class RiskScoringService
{
    /**
     * Calculate automatic risk score (0–1000) from application data.
     *
     * Breakdown (1000 total):
     *  - Affordability / Debt-to-Income    : max 250 pts
     *  - Employment stability              : max 150 pts
     *  - Repayment history (existing loans) : max 250 pts
     *  - Loan-to-Income ratio               : max 100 pts
     *  - Age factor                         : max  80 pts
     *  - Data completeness                  : max  70 pts
     *  - Existing debt burden               : max 100 pts
     *
     * Returns ['score' => int, 'breakdown' => [...], 'label' => string, 'color' => string]
     */
    public function calculate(LoanApplication $application): array
    {
        $breakdown = [];
        $totalScore = 0;

        // ── 1. AFFORDABILITY (max 250) ──────────────────────────────
        $affordMax = 250;
        $afford = $application->affordability;
        if ($afford && $afford->net_salary > 0) {
            $netSalary = (float) $afford->net_salary;
            $disposable = (float) $afford->disposable_income;
            $ratio = $disposable / $netSalary; // higher is better

            if ($ratio >= 0.40) $affordScore = $affordMax;
            elseif ($ratio >= 0.30) $affordScore = (int) ($affordMax * 0.85);
            elseif ($ratio >= 0.20) $affordScore = (int) ($affordMax * 0.60);
            elseif ($ratio >= 0.10) $affordScore = (int) ($affordMax * 0.35);
            else $affordScore = (int) ($affordMax * 0.10);

            $breakdown[] = [
                'factor' => 'Affordability',
                'detail' => 'Disposable income is ' . round($ratio * 100) . '% of net salary',
                'score' => $affordScore,
                'max' => $affordMax,
                'icon' => 'calculator-fill',
                'color' => '#4f46e5',
            ];
        } else {
            $affordScore = 0;
            $breakdown[] = [
                'factor' => 'Affordability',
                'detail' => 'No affordability data available',
                'score' => 0,
                'max' => $affordMax,
                'icon' => 'calculator-fill',
                'color' => '#4f46e5',
            ];
        }
        $totalScore += $affordScore;

        // ── 2. EMPLOYMENT STABILITY (max 150) ───────────────────────
        $empMax = 150;
        $employment = $application->employment;
        $empScore = 0;
        $empDetail = 'No employment data';

        if ($employment) {
            // Employer type bonus
            $govTypes = ['government', 'parastatal', 'ngo', 'international'];
            $empType = strtolower($employment->employer_type ?? '');

            if (in_array($empType, $govTypes)) {
                $empScore += 80; // stable employer
                $empDetail = ucfirst($empType) . ' employee';
            } elseif (!empty($empType)) {
                $empScore += 50; // private sector
                $empDetail = ucfirst($empType) . ' employee';
            }

            // Contract duration — if expiry is far in the future, more stable
            if ($employment->employment_expiry_date) {
                $monthsLeft = now()->diffInMonths($employment->employment_expiry_date, false);
                if ($monthsLeft >= 24) $empScore += 70;
                elseif ($monthsLeft >= 12) $empScore += 50;
                elseif ($monthsLeft >= 6) $empScore += 30;
                elseif ($monthsLeft >= 0) $empScore += 10;
                // expired contract = 0
                $empDetail .= ', contract ' . ($monthsLeft > 0 ? $monthsLeft . ' months remaining' : 'expiring soon');
            } else {
                $empScore += 40; // permanent (no expiry)
                $empDetail .= ', permanent contract';
            }
        }

        $empScore = min($empScore, $empMax);
        $breakdown[] = [
            'factor' => 'Employment Stability',
            'detail' => $empDetail,
            'score' => $empScore,
            'max' => $empMax,
            'icon' => 'briefcase-fill',
            'color' => '#0891b2',
        ];
        $totalScore += $empScore;

        // ── 3. REPAYMENT HISTORY (max 250) ──────────────────────────
        $repMax = 250;
        $userId = $application->user_id;
        $repScore = 0;
        $repDetail = 'New borrower (no history)';

        if ($userId) {
            $pastLoans = Loan::where('user_id', $userId)->get();
            $loanCount = $pastLoans->count();

            if ($loanCount > 0) {
                $closedLoans = $pastLoans->where('status', 'closed')->count();
                $active = $pastLoans->where('status', 'active')->count();
                $defaulted = $pastLoans->whereIn('status', ['defaulted', 'written_off'])->count();
                $overdue = $pastLoans->where('status', 'overdue')->count();

                // Past successfully closed loans (big positive)
                $repScore += min(100, $closedLoans * 40);

                // Active loans in good standing
                $repScore += min(50, $active * 25);

                // Penalty for defaults/overdue
                $repScore -= $defaulted * 80;
                $repScore -= $overdue * 40;

                // Check installment payment record
                $totalInstallments = LoanInstallment::whereIn('loan_id', $pastLoans->pluck('id'))
                    ->count();
                $paidOnTime = LoanInstallment::whereIn('loan_id', $pastLoans->pluck('id'))
                    ->where('status', 'paid')
                    ->where('late_fee', '<=', 0)
                    ->count();

                if ($totalInstallments > 0) {
                    $onTimeRatio = $paidOnTime / $totalInstallments;
                    $repScore += (int) ($onTimeRatio * 100);
                }

                $repScore = max(0, min($repMax, $repScore));
                $repDetail = $closedLoans . ' closed, ' . $active . ' active, ' . $defaulted . ' defaulted';
            } else {
                // First-time borrower gets a neutral score
                $repScore = (int) ($repMax * 0.50);
                $repDetail = 'First-time borrower — neutral score';
            }
        }

        $breakdown[] = [
            'factor' => 'Repayment History',
            'detail' => $repDetail,
            'score' => $repScore,
            'max' => $repMax,
            'icon' => 'clock-history',
            'color' => '#10b981',
        ];
        $totalScore += $repScore;

        // ── 4. LOAN-TO-INCOME RATIO (max 100) ──────────────────────
        $ltiMax = 100;
        $ltiScore = 0;
        $ltiDetail = 'No salary data';

        if ($afford && $afford->net_salary > 0 && $application->requested_amount > 0) {
            $ltiRatio = $application->requested_amount / ($afford->net_salary * 12);
            if ($ltiRatio <= 0.3) $ltiScore = $ltiMax;
            elseif ($ltiRatio <= 0.5) $ltiScore = (int) ($ltiMax * 0.75);
            elseif ($ltiRatio <= 1.0) $ltiScore = (int) ($ltiMax * 0.50);
            elseif ($ltiRatio <= 2.0) $ltiScore = (int) ($ltiMax * 0.25);
            else $ltiScore = 0;

            $ltiDetail = 'Loan is ' . round($ltiRatio * 100) . '% of annual income';
        }

        $breakdown[] = [
            'factor' => 'Loan-to-Income',
            'detail' => $ltiDetail,
            'score' => $ltiScore,
            'max' => $ltiMax,
            'icon' => 'cash-coin',
            'color' => '#f59e0b',
        ];
        $totalScore += $ltiScore;

        // ── 5. AGE FACTOR (max 80) ──────────────────────────────────
        $ageMax = 80;
        $ageScore = 0;
        $ageDetail = 'No date of birth';

        if ($application->date_of_birth) {
            $age = (int) now()->diffInYears($application->date_of_birth);
            if ($age >= 25 && $age <= 55) $ageScore = $ageMax;
            elseif ($age >= 21 && $age < 25) $ageScore = (int) ($ageMax * 0.70);
            elseif ($age > 55 && $age <= 65) $ageScore = (int) ($ageMax * 0.60);
            elseif ($age > 65) $ageScore = (int) ($ageMax * 0.30);
            else $ageScore = (int) ($ageMax * 0.20); // under 21

            $ageDetail = $age . ' years old';
        }

        $breakdown[] = [
            'factor' => 'Age Factor',
            'detail' => $ageDetail,
            'score' => $ageScore,
            'max' => $ageMax,
            'icon' => 'person-fill',
            'color' => '#8b5cf6',
        ];
        $totalScore += $ageScore;

        // ── 6. DATA COMPLETENESS (max 70) ───────────────────────────
        $dataMax = 70;
        $dataScore = 0;
        $checks = 0;
        $passed = 0;

        // Personal info
        $checks++; if ($application->national_id) $passed++;
        $checks++; if ($application->cell_number) $passed++;
        $checks++; if ($application->email) $passed++;
        $checks++; if ($application->date_of_birth) $passed++;
        $checks++; if ($application->residential_address) $passed++;

        // Employment
        $checks++; if ($employment && $employment->employer_name) $passed++;

        // Bank details
        $checks++; if ($application->bankDetails) $passed++;

        // Documents
        $docCount = $application->documents->count();
        $checks++; if ($docCount >= 2) $passed++;

        // Next of kin
        $checks++; if ($application->nextOfKin->count() > 0) $passed++;

        $dataScore = $checks > 0 ? (int) (($passed / $checks) * $dataMax) : 0;
        $dataDetail = $passed . '/' . $checks . ' fields complete';

        $breakdown[] = [
            'factor' => 'Data Completeness',
            'detail' => $dataDetail,
            'score' => $dataScore,
            'max' => $dataMax,
            'icon' => 'check2-all',
            'color' => '#64748b',
        ];
        $totalScore += $dataScore;

        // ── 7. EXISTING DEBT BURDEN (max 100) ───────────────────────
        $debtMax = 100;
        $debtScore = $debtMax; // start with full marks, deduct
        $debtDetail = 'No existing debt';

        if ($afford) {
            $existingLoans = (float) $afford->existing_loans_deduction;
            $otherRepayments = (float) $afford->other_loan_repayments;
            $totalDebt = $existingLoans + $otherRepayments;

            if ($afford->net_salary > 0) {
                $debtRatio = $totalDebt / $afford->net_salary;
                if ($debtRatio >= 0.40) $debtScore = 0;
                elseif ($debtRatio >= 0.30) $debtScore = (int) ($debtMax * 0.25);
                elseif ($debtRatio >= 0.20) $debtScore = (int) ($debtMax * 0.50);
                elseif ($debtRatio >= 0.10) $debtScore = (int) ($debtMax * 0.75);
                else $debtScore = $debtMax;

                $debtDetail = round($debtRatio * 100) . '% of salary goes to existing debt';
            }
        }

        $breakdown[] = [
            'factor' => 'Existing Debt Burden',
            'detail' => $debtDetail,
            'score' => $debtScore,
            'max' => $debtMax,
            'icon' => 'exclamation-triangle-fill',
            'color' => '#ef4444',
        ];
        $totalScore += $debtScore;

        // ── FINAL ───────────────────────────────────────────────────
        $totalScore = max(0, min(1000, $totalScore));

        if ($totalScore >= 700) {
            $label = 'Low Risk';
            $color = '#10b981';
        } elseif ($totalScore >= 500) {
            $label = 'Moderate Risk';
            $color = '#f59e0b';
        } else {
            $label = 'High Risk';
            $color = '#ef4444';
        }

        return [
            'score' => $totalScore,
            'breakdown' => $breakdown,
            'label' => $label,
            'color' => $color,
        ];
    }
}
