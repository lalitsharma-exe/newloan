<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AffordabilityAssessment extends Model {
    protected $table = 'affordability_assessments';

    protected $fillable = [
        'application_id',
        'monthly_earnings','tax_deduction','existing_loans_deduction','other_deductions',
        'net_salary','transport','groceries','utilities','rent','other_expenses',
        'total_living_expenses','disposable_income','suggested_loan_amount','max_loan_amount',
    ];

    public function application() { return $this->belongsTo(LoanApplication::class); }
}