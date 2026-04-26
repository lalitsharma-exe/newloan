<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBillLimit extends Model
{
    protected $table = 'mybill_limits';

    protected $fillable = [
        'user_id',
        'total_limit',
        'used_amount',
        'activated_at',
    ];

    protected $casts = [
        'total_limit'  => 'decimal:2',
        'used_amount'  => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function user() { return $this->belongsTo(User::class); }

    // ── Accessors ─────────────────────────────────────────────────
    public function getAvailableAmountAttribute(): float
    {
        return max(0, round((float) $this->total_limit - (float) $this->used_amount, 2));
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Check if the client can afford a bill of given value.
     */
    public function canAfford(float $billValue): bool
    {
        return $billValue > 0 && $billValue <= $this->available_amount;
    }

    /**
     * Reserve limit for a purchase (increase used_amount).
     */
    public function reserve(float $billValue): void
    {
        $this->increment('used_amount', $billValue);
    }

    /**
     * Restore limit after a loan is settled or failed (decrease used_amount).
     */
    public function restore(float $billValue): void
    {
        $this->decrement('used_amount', min($billValue, (float) $this->used_amount));
    }
}
