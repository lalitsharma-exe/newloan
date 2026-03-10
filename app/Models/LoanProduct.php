<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class LoanProduct extends Model {
    protected $fillable = ["name","slug","interest_rate","min_amount","max_amount","min_term_months","max_term_months","processing_fee","processing_fee_type","late_payment_fee","description","eligibility_criteria","is_active"];
    protected $casts    = ["interest_rate"=>"decimal:4","min_amount"=>"decimal:2","max_amount"=>"decimal:2","processing_fee"=>"decimal:2","late_payment_fee"=>"decimal:2","is_active"=>"boolean"];
    public function applications() { return $this->hasMany(LoanApplication::class); }
    public function loans()        { return $this->hasMany(Loan::class); }
    public function scopeActive($q){ return $q->where("is_active",true); }
}
