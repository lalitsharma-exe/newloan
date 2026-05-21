<?php
namespace App\Services\Admin;

use App\Models\LoanApplication;
use App\Models\Loan;
use Carbon\Carbon;

class ScoringService
{
    /**
     * Calculate Credit Score (Ability to Pay) - 100 Points
     */
    public function calculateCreditScore(LoanApplication $app): array
    {
        $points = 0;
        $breakdown = [];

        // A. Repayment History (35 pts)
        $historyPoints = 35; // Default for new users? Or maybe 25?
        $user = $app->user;
        if ($user) {
            $pastLoans = Loan::where('user_id', $user->id)->get();
            if ($pastLoans->isNotEmpty()) {
                $defaults = $pastLoans->where('status', 'defaulted')->count();
                $overdue = $pastLoans->where('status', 'overdue')->count();
                
                if ($defaults > 0) {
                    $historyPoints = 0;
                } elseif ($overdue > 2) {
                    $historyPoints = 10;
                } elseif ($overdue > 0) {
                    $historyPoints = 25;
                } else {
                    $historyPoints = 35;
                }
            }
        }
        $points += $historyPoints;
        $breakdown['repayment_history'] = $historyPoints;

        // B. Affordability (20 pts)
        $affordPoints = 0;
        $affordability = $app->affordability;
        $requestedTerm = (int)$app->requested_term;
        if ($affordability && $requestedTerm > 0) {
            $product = $app->loanProduct;
            $interestRate = $product ? (float)$product->interest_rate : 15.0;
            $monthlyInst = app(ApplicationService::class)->calcMonthly(
                $app->requested_amount, 
                $interestRate, 
                $requestedTerm, 
                $product
            );
            $netSalary = (float)$affordability->net_salary;
            if ($netSalary > 0) {
                $ratio = ($monthlyInst / $netSalary) * 100;
                if ($ratio < 20) $affordPoints = 20;
                elseif ($ratio <= 35) $affordPoints = 15;
                elseif ($ratio <= 50) $affordPoints = 8;
                else $affordPoints = 0;
            }
        }
        $points += $affordPoints;
        $breakdown['affordability'] = $affordPoints;

        // C. Loan-to-Income (10 pts)
        $ltiPoints = 0;
        if ($affordability && (float)$affordability->net_salary > 0) {
            $ratio = ($app->requested_amount / (float)$affordability->net_salary) * 100;
            if ($ratio < 30) $ltiPoints = 10;
            elseif ($ratio <= 60) $ltiPoints = 6;
            else $ltiPoints = 0;
        }
        $points += $ltiPoints;
        $breakdown['loan_to_income'] = $ltiPoints;

        // D. Employment Stability (15 pts)
        $stabilityPoints = 3;
        $employment = $app->employment;
        if ($employment) {
            $type = strtolower($employment->employer_type);
            if (str_contains($type, 'government') || str_contains($type, 'civil')) $stabilityPoints = 15;
            elseif (str_contains($type, 'private') || str_contains($type, 'corporate')) $stabilityPoints = 12;
            elseif (str_contains($type, 'informal') || str_contains($type, 'sme')) $stabilityPoints = 8;
            else $stabilityPoints = 3;
        }
        $points += $stabilityPoints;
        $breakdown['employment_stability'] = $stabilityPoints;

        // E. Existing Debt Burden (10 pts)
        $debtPoints = 10;
        if ($affordability) {
            $totalDebt = (float)($affordability->total_expenses ?? 0); // Assuming expenses include debt or we have a specific debt field
            if ($totalDebt > 0) {
                $debtPoints = 5;
                if ($totalDebt > ($affordability->net_salary * 0.5)) $debtPoints = 0;
            }
        }
        $points += $debtPoints;
        $breakdown['debt_burden'] = $debtPoints;

        // F. Data Completeness (5 pts)
        $completePoints = 0;
        $docs = $app->documents->where('status', 'verified')->count();
        if ($docs >= 3) $completePoints = 5;
        elseif ($docs >= 1) $completePoints = 3;
        $points += $completePoints;
        $breakdown['data_completeness'] = $completePoints;

        // G. Age Factor (5 pts)
        $agePoints = 0;
        if ($app->date_of_birth) {
            $age = $app->date_of_birth->age;
            if ($age >= 25 && $age <= 55) $agePoints = 5;
            elseif ($age >= 21) $agePoints = 3;
        }
        $points += $agePoints;
        $breakdown['age_factor'] = $agePoints;

        return [
            'total'     => $points,
            'breakdown' => $breakdown,
            'label'     => $this->getCreditLabel($points)
        ];
    }

    /**
     * Calculate Fraud Score (Intent to Cheat) - 100 Points (Starts at 100, subtracts for risks)
     */
    public function calculateFraudScore(LoanApplication $app): array
    {
        $points = 100;
        $breakdown = [];

        // A. Identity Mismatch (-25 pts)
        // Manual check flag or automated? Let's check for duplicate IDs
        $duplicateId = LoanApplication::where('national_id', $app->national_id)
            ->where('id', '!=', $app->id)
            ->where('user_id', '!=', $app->user_id)
            ->exists();
        if ($duplicateId) {
            $points -= 25;
            $breakdown['identity_mismatch'] = -25;
        }

        // B. Device Reuse (-20 pts)
        // This needs session/IP tracking. For now, check if multiple users used the same email domain or similar?
        // Let's check if the user has multiple accounts
        
        // C. Payment Method Risk (-15 pts)
        $bank = $app->bankDetails;
        if ($bank && $app->applicant_name) {
            // Simple name match check
            if (strtolower($bank->account_holder_name) !== strtolower($app->applicant_name)) {
                $points -= 15;
                $breakdown['payment_method_risk'] = -15;
            }
        }

        // D. Active Default (CRITICAL) - HANDLED IN DECISION ENGINE
        $hasDefault = Loan::where('user_id', $app->user_id)->where('status', 'defaulted')->exists();
        if ($hasDefault) {
            $breakdown['active_default'] = 'CRITICAL';
        }

        // E. Application Behavior (-10 pts)
        $recentApps = LoanApplication::where('user_id', $app->user_id)
            ->where('created_at', '>', now()->subDays(7))
            ->count();
        if ($recentApps > 2) {
            $points -= 10;
            $breakdown['application_behavior'] = -10;
        }

        // F. Income vs Loan Mismatch (-15 pts)
        if ($app->affordability && $app->requested_amount > ($app->affordability->net_salary * 5)) {
            $points -= 15;
            $breakdown['income_loan_mismatch'] = -15;
        }

        // G. Location Risk (-10 pts)
        // Handled via IP/GPS if we have it
        if (!$app->gps_latitude || !$app->gps_longitude) {
            // $points -= 5; // Mild penalty for no GPS?
        }

        return [
            'total'     => max(0, $points),
            'breakdown' => $breakdown,
            'label'     => $this->getFraudLabel($points),
            'has_default' => $hasDefault
        ];
    }

    public function getDecision(int $credit, int $fraud, bool $hasDefault = false): array
    {
        if ($hasDefault || $fraud < 60) {
            return ['status' => 'DECLINE', 'reason' => $hasDefault ? 'Active Default detected' : 'High Fraud Risk'];
        }

        if ($credit >= 80 && $fraud >= 80) {
            return ['status' => 'FULL APPROVAL', 'color' => '#10b981'];
        }

        if ($credit >= 65 && $fraud >= 80) {
            return ['status' => 'REDUCED APPROVAL', 'color' => '#f59e0b'];
        }

        if ($fraud >= 60 && $fraud < 80 || ($credit >= 50 && $credit < 65)) {
            return ['status' => 'MANUAL REVIEW', 'color' => '#6366f1'];
        }

        if ($credit >= 50 && $fraud >= 80) {
            return ['status' => 'SMALL LOAN ONLY', 'color' => '#f59e0b'];
        }

        return ['status' => 'DECLINE', 'reason' => 'Low Credit Score'];
    }

    private function getCreditLabel(int $score): string
    {
        if ($score >= 80) return 'STRONG';
        if ($score >= 65) return 'GOOD';
        if ($score >= 50) return 'WEAK';
        return 'DECLINE';
    }

    private function getFraudLabel(int $score): string
    {
        if ($score >= 80) return 'SAFE';
        if ($score >= 60) return 'REVIEW';
        if ($score >= 40) return 'HIGH RISK';
        return 'DECLINE';
    }
}
