<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'investor_id',
        'contract_ref',
        'principal_cents',
        'investment_date',
        'interest_rate',
        'interest_start_date',
        'maturity_date',
        'total_months',
        'monthly_interest_cents',
        'total_interest_cents',
        'total_repayable_cents',
        'status',
        'contract_url',
        'treasury_account_id',
        'termination_requested_at',
        'termination_notice_expiry',
        'termination_approved_by',
        'termination_fee_cents',
        'net_payout_cents',
        'terminated_at',
        'repaid_at',
    ];

    protected $casts = [
        'investment_date' => 'date',
        'interest_start_date' => 'date',
        'maturity_date' => 'date',
        'termination_notice_expiry' => 'date',
        'termination_requested_at' => 'datetime',
        'terminated_at' => 'datetime',
        'repaid_at' => 'datetime',
        'interest_rate' => 'decimal:4',
    ];

    /**
     * Get the investor that owns this investment.
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Get the accrual schedules for this investment.
     */
    public function accruals(): HasMany
    {
        return $this->hasMany(InvestmentAccrual::class);
    }

    /**
     * Get the treasury account this investment is funded into.
     */
    public function treasuryAccount(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class);
    }

    /**
     * Get the user who approved early termination.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'termination_approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS (For standard decimal display)
    |--------------------------------------------------------------------------
    */

    public function getPrincipalAttribute(): float
    {
        return $this->principal_cents / 100;
    }

    public function getMonthlyInterestAttribute(): float
    {
        return $this->monthly_interest_cents / 100;
    }

    public function getTotalInterestAttribute(): float
    {
        return $this->total_interest_cents / 100;
    }

    public function getTotalRepayableAttribute(): float
    {
        return $this->total_repayable_cents / 100;
    }

    public function getTerminationFeeAttribute(): ?float
    {
        return $this->termination_fee_cents ? ($this->termination_fee_cents / 100) : null;
    }

    public function getNetPayoutAttribute(): ?float
    {
        return $this->net_payout_cents ? ($this->net_payout_cents / 100) : null;
    }
}
