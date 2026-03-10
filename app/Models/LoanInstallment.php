<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoanInstallment extends Model {
    protected $fillable = [
        "loan_id","installment_number","due_date",
        "principal_amount","interest_amount","total_amount",
        "paid_amount","outstanding_amount","late_fee",
        "status","paid_at",
    ];

    protected $casts = [
        "due_date" => "date",
        "paid_at"  => "datetime",
    ];

    public function loan()    { return $this->belongsTo(Loan::class); }
    public function payment() { return $this->belongsTo(Payment::class, "installment_id"); }
}