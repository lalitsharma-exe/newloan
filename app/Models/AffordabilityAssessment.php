<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AffordabilityAssessment extends Model {
    protected $fillable = ['application_id','monthly_earnings','tax_deduction','existing_loans_deduction','other_deductions','net_salary','transport','groceries','utilities','rent','other_expenses','total_living_expenses','disposable_income','suggested_loan_amount','max_loan_amount'];
    protected $casts = ['monthly_earnings'=>'decimal:2','net_salary'=>'decimal:2','disposable_income'=>'decimal:2','suggested_loan_amount'=>'decimal:2','max_loan_amount'=>'decimal:2'];
    public function application() { return $this->belongsTo(LoanApplication::class); }
}
