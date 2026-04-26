<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBillRepayment extends Model
{
    protected $table = 'mybill_repayments';

    protected $fillable = [
        'mybill_loan_id',
        'user_id',
        'amount',
        'deduction_type',
        'status',
        'salary_credit_ref',
        'cpay_txn_id',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function loan() { return $this->belongsTo(MyBillLoan::class, 'mybill_loan_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
