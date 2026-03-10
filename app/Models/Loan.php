<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Loan extends Model {
    use SoftDeletes;
    protected $fillable = ["loan_number","user_id","loan_product_id","application_id","principal_amount","interest_rate","term_months","total_amount","outstanding_balance","monthly_installment","processing_fee","status","disbursement_date","maturity_date","first_payment_date","last_payment_date","payout_method","collection_method","closed_at","closed_reason","closed_by"];
    protected $casts = ["principal_amount"=>"decimal:2","interest_rate"=>"decimal:4","total_amount"=>"decimal:2","outstanding_balance"=>"decimal:2","monthly_installment"=>"decimal:2","processing_fee"=>"decimal:2","disbursement_date"=>"date","maturity_date"=>"date","first_payment_date"=>"date","last_payment_date"=>"date","closed_at"=>"datetime"];
    const STATUS_ACTIVE="active"; const STATUS_OVERDUE="overdue"; const STATUS_PAID_OFF="paid_off"; const STATUS_CLOSED="closed";
    public function user()        { return $this->belongsTo(User::class); }
    public function loanProduct() { return $this->belongsTo(LoanProduct::class); }
    public function application() { return $this->belongsTo(LoanApplication::class,"application_id"); }
    public function installments(){ return $this->hasMany(LoanInstallment::class)->orderBy("due_date"); }
    public function payments()    { return $this->hasMany(Payment::class)->latest(); }
    public function getDaysOverdueAttribute():int {
        $o=$this->installments()->where("status","overdue")->orderBy("due_date")->first();
        return $o ? (int)$o->due_date->diffInDays(now()) : 0;
    }
    public function scopeActive($q)  { return $q->where("status","active"); }
    public function scopeOverdue($q) { return $q->where("status","overdue"); }
}
