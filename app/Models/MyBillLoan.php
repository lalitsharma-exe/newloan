<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBillLoan extends Model
{
    protected $table = 'mybill_loans';

    protected $fillable = [
        'loan_number',
        'user_id',
        'bill_value',
        'loan_amount',
        'tier',
        'upfront_amount',
        'payday_amount',
        'settled_amount',
        'bill_category',
        'provider_ref',
        'provider_response',
        'cpay_txn_id',
        'meter_number',
        'phone_number',
        'airtime_type',
        'policy_number',
        'insurance_partner_id',
        'event_id',
        'ticket_id',
        'ticket_reference',
        'disbursed_at',
        'status',
        'payday_deducted_at',
        'failure_reason',
    ];

    protected $casts = [
        'bill_value'      => 'decimal:2',
        'loan_amount'     => 'decimal:2',
        'upfront_amount'  => 'decimal:2',
        'payday_amount'   => 'decimal:2',
        'settled_amount'  => 'decimal:2',
        'disbursed_at'    => 'datetime',
        'payday_deducted_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function user()       { return $this->belongsTo(User::class); }
    public function repayments() { return $this->hasMany(MyBillRepayment::class, 'mybill_loan_id'); }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($q)  { return $q->whereIn('status', ['active', 'partial']); }
    public function scopeSettled($q) { return $q->where('status', 'settled'); }

    // ── Accessors ─────────────────────────────────────────────────

    public function getOutstandingAmountAttribute(): float
    {
        return max(0, round((float) $this->payday_amount - (float) $this->settled_amount, 2));
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['active', 'partial']);
    }

    public function getIsSettledAttribute(): bool
    {
        return $this->status === 'settled';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => 'w',
            'settled'  => 'ok',
            'partial'  => 'i',
            'failed'   => 'e',
            'refunded' => 's',
            'pending'  => 's',
            default    => 's',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->bill_category) {
            'electricity' => 'lightning-charge-fill',
            'airtime'     => 'phone-fill',
            'insurance'   => 'shield-fill',
            'ticket'      => 'ticket-perforated-fill',
            default       => 'receipt',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->bill_category) {
            'electricity' => '#f59e0b',
            'airtime'     => '#06b6d4',
            'insurance'   => '#8b5cf6',
            'ticket'      => '#ec4899',
            default       => '#6b7280',
        };
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Generate the next sequential loan number.
     */
    public static function generateLoanNumber(): string
    {
        $nextId = (self::max('id') ?? 0) + 1;
        return 'MBILL-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Record a settlement payment.
     */
    public function recordSettlement(float $amount): void
    {
        $this->increment('settled_amount', $amount);
        $this->refresh();

        if ($this->outstanding_amount <= 0) {
            $this->update([
                'status'            => 'settled',
                'payday_deducted_at' => now(),
            ]);
        } else {
            $this->update(['status' => 'partial']);
        }
    }
}
