<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquiditySnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'cash_available',
        'expected_inflows_30d',
        'expected_outflows_30d',
        'net_liquidity',
        'runway_days',
        'daily_burn_rate'
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'cash_available' => 'decimal:2',
        'expected_inflows_30d' => 'decimal:2',
        'expected_outflows_30d' => 'decimal:2',
        'net_liquidity' => 'decimal:2',
        'daily_burn_rate' => 'decimal:2'
    ];
}
