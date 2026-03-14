<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class LoanProduct extends Model {
    protected $fillable = [
        "name","slug","interest_rate","interest_method",
        "initiation_fee_rate","admin_fee_fixed",
        "min_amount","max_amount","min_term_months","max_term_months","max_term_months_allowed",
        "processing_fee","processing_fee_type","late_payment_fee",
        "description","eligibility_criteria","is_active",
    ];
    protected $casts = [
        "interest_rate"       => "decimal:2",
        "initiation_fee_rate" => "decimal:2",
        "admin_fee_fixed"     => "decimal:2",
        "min_amount"          => "decimal:2",
        "max_amount"          => "decimal:2",
        "processing_fee"      => "decimal:2",
        "late_payment_fee"    => "decimal:2",
        "is_active"           => "boolean",
    ];
    public function applications() { return $this->hasMany(LoanApplication::class); }
    public function loans()        { return $this->hasMany(Loan::class); }
    public function scopeActive($q){ return $q->where("is_active",true); }
}

// Note: scopeActive already may exist, this is a safe append check
