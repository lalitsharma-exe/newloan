<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentAccrual extends Model
{
    use HasUuids;

    protected $fillable = [
        'investment_id',
        'accrual_date',
        'interest_cents',
        'status',
        'posted_at',
    ];

    protected $casts = [
        'accrual_date' => 'date',
        'posted_at' => 'datetime',
    ];

    /**
     * Get the investment this accrual belongs to.
     */
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    public function getInterestAttribute(): float
    {
        return $this->interest_cents / 100;
    }
}
