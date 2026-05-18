<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsEquityMovement extends Model
{
    protected $table = 'fs_equity_movements';

    protected $fillable = [
        'period_id',
        'movement_type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function period()
    {
        return $this->belongsTo(FinancialPeriod::class, 'period_id');
    }

    /**
     * Compute total prior year adjustments recorded for a given year.
     */
    public static function priorYearAdjustments(int $year): float
    {
        return (float) self::where('movement_type', 'prior_year_adj')
            ->whereHas('period', function ($q) use ($year) {
                $q->where('year', $year);
            })
            ->sum('amount');
    }
}
