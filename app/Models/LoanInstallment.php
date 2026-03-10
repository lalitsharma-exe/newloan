<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanInstallment extends Model {
    protected $fillable = ["loan_id","installment_number","due_date","principal_amount","interest_amount","total_amount","paid_amount","outstanding_amount","late_fee","status","paid_at"];
    protected $casts = ["due_date"=>"date","paid_at"=>"datetime","principal_amount"=>"decimal:2","interest_amount"=>"decimal:2","total_amount"=>"decimal:2","paid_amount"=>"decimal:2","outstanding_amount"=>"decimal:2","late_fee"=>"decimal:2"];
    const STATUS_PENDING="pending"; const STATUS_PAID="paid"; const STATUS_PARTIAL="partial"; const STATUS_OVERDUE="overdue";
    public function loan()     { return $this->belongsTo(Loan::class); }
    public function payments() { return $this->hasMany(Payment::class,"installment_id"); }
}
