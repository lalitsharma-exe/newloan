<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    protected $fillable = ["payment_reference","loan_id","installment_id","user_id","amount","method","gateway_reference","reference","status","notes","is_manual","verified_by","verified_at"];
    protected $casts = ["amount"=>"decimal:2","verified_at"=>"datetime","is_manual"=>"boolean"];
    const STATUS_PENDING="pending"; const STATUS_VERIFIED="verified"; const STATUS_REJECTED="rejected";
    public function loan()        { return $this->belongsTo(Loan::class); }
    public function installment() { return $this->belongsTo(LoanInstallment::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function verifiedBy()  { return $this->belongsTo(User::class,"verified_by"); }
    public function getStatusBadgeAttribute():string {
        return match($this->status){"verified"=>"success","pending"=>"warning","rejected"=>"danger",default=>"secondary"};
    }
}
