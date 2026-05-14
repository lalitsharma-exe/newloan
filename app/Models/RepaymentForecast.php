<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepaymentForecast extends Model
{
    protected $fillable = [
        'loan_id',
        'user_id',
        'expected_amount',
        'probability',
        'forecast_date'
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'probability' => 'decimal:2',
        'forecast_date' => 'date'
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
