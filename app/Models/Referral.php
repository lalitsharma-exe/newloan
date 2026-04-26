<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'loan_id',
        'status',
        'is_first_loan',
        'is_repeat_loan',
        'rejected_reason',
        'amount',
        'qualified_at',
        'paid_at',
        'payout_method',
    ];

    protected $casts = [
        'is_first_loan' => 'boolean',
        'is_repeat_loan' => 'boolean',
        'qualified_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
