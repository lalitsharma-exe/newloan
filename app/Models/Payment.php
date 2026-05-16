<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    protected $fillable = [
        "payment_reference","loan_id","installment_id","user_id","application_id",
        "amount","method","gateway_reference","reference",
        "status","notes","is_manual",
        "verified_by","verified_at",
        "principal_portion","interest_portion","initiation_fee_portion","admin_fee_portion","penalty_portion","repayment_components"
    ];

    protected $casts = [
        "verified_at" => "datetime",
        "is_manual"   => "boolean",
    ];

    public function application() { return $this->belongsTo(LoanApplication::class, "application_id"); }
    public function loan()       { return $this->belongsTo(Loan::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function installment(){ return $this->belongsTo(LoanInstallment::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, "verified_by"); }

    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'verified' => 'ok',
            'pending'  => 'w',
            'rejected',
            'failed'   => 'e',
            default    => 's',
        };
    }
}