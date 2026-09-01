<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model {
    protected $fillable = [
        'name', 'slug', 'interest_rate', 'interest_method',
        'initiation_fee_rate',  // % of principal charged once at disbursement
        'admin_fee_fixed',      // fixed M amount per month
        'late_payment_fee',     // M per 10-day overdue period
        'min_amount', 'max_amount',
        'min_term_months', 'max_term_months', 'max_term_months_allowed',
        'description', 'eligibility_criteria', 'is_active',
    ];

    protected $casts = [
        'interest_rate'        => 'decimal:2',
        'initiation_fee_rate'  => 'decimal:2',
        'admin_fee_fixed'      => 'decimal:2',
        'min_amount'           => 'decimal:2',
        'max_amount'           => 'decimal:2',
        'late_payment_fee'     => 'decimal:2',
        'is_active'            => 'boolean',
    ];

    public function applications() { return $this->hasMany(LoanApplication::class); }
    public function loans()        { return $this->hasMany(Loan::class); }
    public function scopeActive($q){ return $q->where('is_active', true); }

    /**
     * Calculate the monthly installment for a given principal and term.
     * Supports both 'reducing' and 'flat' interest calculation methods.
     */
    public function calcMonthly(float $principal, int $term): float
    {
        if ($term <= 0) return 0.0;
        $rate          = (float) $this->interest_rate / 100;
        $initiationFee = round($principal * ((float) $this->initiation_fee_rate / 100), 2);
        $adminPerMonth = (float) ($this->admin_fee_fixed ?? 0);
        $totalAdmin    = $adminPerMonth * $term;

        if ($this->interest_method === 'reducing') {
            $pmt = ($rate > 0)
                ? ($principal * $rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1)
                : ($principal / $term);
            $monthly = $pmt + $adminPerMonth + ($initiationFee / $term);
            return round($monthly, 2);
        }

        $totalInterest = round($principal * $rate * $term, 2);
        $totalRepay    = $principal + $totalInterest + $initiationFee + $totalAdmin;
        return round($totalRepay / $term, 2);
    }

    /**
     * Validate amount and term against product limits.
     * Returns error string or null if valid.
     */
    public function validateLimits(float $amount, int $term): ?string
    {
        if ($amount < $this->min_amount)
            return "Amount M{$amount} is below the minimum of M{$this->min_amount} for {$this->name}.";
        if ($amount > $this->max_amount)
            return "Amount M{$amount} exceeds the maximum of M{$this->max_amount} for {$this->name}.";
        if ($term < $this->min_term_months)
            return "Term {$term} months is below the minimum of {$this->min_term_months} months for {$this->name}.";
        if ($term > $this->max_term_months)
            return "Term {$term} months exceeds the maximum of {$this->max_term_months} months for {$this->name}.";
        return null;
    }
}

