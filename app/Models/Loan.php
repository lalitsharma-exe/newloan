<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_number',
        'disbursement_reference',
        'user_id',
        'loan_product_id',
        'application_id',
        'status',
        'principal_amount',
        'interest_rate',
        'term_months',
        'total_amount',
        'outstanding_balance',
        'monthly_installment',
        'processing_fee',          // DB column kept; labelled "Initiation Fee" in UI
        'disbursement_date',
        'maturity_date',
        'first_payment_date',
        'last_payment_date',
        'payout_method',
        'collection_method',
        'salary_payday',
        'disbursement_method',
        'disbursement_phone',
        'disbursement_provider',
        'closed_at',
        'closed_reason',
        'closed_by',
    ];

    protected $casts = [
        'disbursement_date'  => 'date',
        'maturity_date'      => 'date',
        'first_payment_date' => 'date',
        'last_payment_date'  => 'date',
        'closed_at'          => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($q)  { return $q->where('status', 'active'); }
    public function scopeOverdue($q) { return $q->where('status', 'overdue'); }
    public function scopePaidOff($q) { return $q->where('status', 'paid_off'); }
    public function scopeClosed($q)  { return $q->where('status', 'closed'); }

    // ── Relationships ─────────────────────────────────────────────
    public function user()         { return $this->belongsTo(User::class); }
    public function loanProduct()  { return $this->belongsTo(LoanProduct::class); }
    public function application()  { return $this->belongsTo(LoanApplication::class, 'application_id'); }
    public function installments() { return $this->hasMany(LoanInstallment::class)->orderBy('installment_number'); }
    public function payments()     { return $this->hasMany(Payment::class); }
    public function closedBy()     { return $this->belongsTo(User::class, 'closed_by'); }

    // ── Accessors ─────────────────────────────────────────────────

    // "Initiation Fee" is the correct term — processing_fee is the DB column name
    public function getInitiationFeeAttribute(): float
    {
        return (float) $this->processing_fee;
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->status !== 'overdue') return 0;
        $inst = $this->installments()
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->first();
        return $inst ? now()->diffInDays($inst->due_date) : 0;
    }
}
