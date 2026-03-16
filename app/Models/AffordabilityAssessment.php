<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffordabilityAssessment extends Model
{
    protected $fillable = [
        'application_id',
        'monthly_earnings',
        'tax_deduction',
        'existing_loans_deduction',
        'other_deductions',
        'net_salary',
        'transport',
        'groceries',
        'utilities',
        'rent',
        'other_expenses',
        'education',
        'communication',
        'other_insurance',
        'medical',
        'other_loan_repayments',
        'family_support',
        'entertainment',
        'total_living_expenses',
        'disposable_income',
        'suggested_loan_amount',
        'max_loan_amount',
    ];

    protected $casts = [
        'monthly_earnings'         => 'decimal:2',
        'tax_deduction'            => 'decimal:2',
        'existing_loans_deduction' => 'decimal:2',
        'other_deductions'         => 'decimal:2',
        'net_salary'               => 'decimal:2',
        'transport'                => 'decimal:2',
        'groceries'                => 'decimal:2',
        'utilities'                => 'decimal:2',
        'rent'                     => 'decimal:2',
        'other_expenses'           => 'decimal:2',
        'education'                => 'decimal:2',
        'communication'            => 'decimal:2',
        'other_insurance'          => 'decimal:2',
        'medical'                  => 'decimal:2',
        'other_loan_repayments'    => 'decimal:2',
        'family_support'           => 'decimal:2',
        'entertainment'            => 'decimal:2',
        'total_living_expenses'    => 'decimal:2',
        'disposable_income'        => 'decimal:2',
        'suggested_loan_amount'    => 'decimal:2',
        'max_loan_amount'          => 'decimal:2',
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplication::class, 'application_id');
    }

    public function recalculate(): void
    {
        $totalDeductions = (float)$this->tax_deduction
            + (float)$this->existing_loans_deduction
            + (float)$this->other_deductions;

        $netSalary = (float)$this->monthly_earnings - $totalDeductions;

        $totalExpenses = (float)$this->transport
            + (float)$this->groceries
            + (float)$this->utilities
            + (float)$this->rent
            + (float)$this->other_expenses
            + (float)$this->education
            + (float)$this->communication
            + (float)$this->other_insurance
            + (float)$this->medical
            + (float)$this->other_loan_repayments
            + (float)$this->family_support
            + (float)$this->entertainment;

        $this->net_salary            = round($netSalary, 2);
        $this->total_living_expenses = round($totalExpenses, 2);
        $this->disposable_income     = round($netSalary - $totalExpenses, 2);
    }
}
