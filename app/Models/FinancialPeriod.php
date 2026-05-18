<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    protected $fillable = [
        'period_label',
        'period_end_date',
        'year',
        'quarter',
        'month',
        'status',
    ];

    protected $casts = [
        'period_end_date' => 'date',
        'year'            => 'integer',
        'quarter'         => 'integer',
        'month'           => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function revenueLines()    { return $this->hasMany(FsRevenueLine::class, 'period_id'); }
    public function expenseLines()    { return $this->hasMany(FsExpenseLine::class, 'period_id'); }
    public function provisions()      { return $this->hasMany(FsProvision::class, 'period_id'); }
    public function equityMovements() { return $this->hasMany(FsEquityMovement::class, 'period_id'); }
    public function cashFlowLines()   { return $this->hasMany(FsCashFlowLine::class, 'period_id'); }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeForYearMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)
            ->where('month', $month)
            ->whereNull('quarter');
    }

    public function scopeForQuarter($query, int $year, int $quarter)
    {
        return $query->where('year', $year)
            ->where('quarter', $quarter)
            ->whereNull('month');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year)
            ->where('period_label', $year . '-annual');
    }
}
