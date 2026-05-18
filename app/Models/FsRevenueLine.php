<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsRevenueLine extends Model
{
    protected $table = 'fs_revenue_lines';

    protected $fillable = [
        'period_id',
        'revenue_type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function period()
    {
        return $this->belongsTo(FinancialPeriod::class, 'period_id');
    }
}
