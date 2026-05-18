<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsCashFlowLine extends Model
{
    protected $table = 'fs_cash_flow_lines';

    protected $fillable = [
        'period_id',
        'line_code',
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
