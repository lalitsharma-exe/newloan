<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloatPenaltyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'float_record_id',
        'penalty_amount',
        'balance_before',
        'balance_after',
        'penalty_date',
        'notification_sent',
    ];

    protected $casts = [
        'penalty_date' => 'date',
        'penalty_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'notification_sent' => 'boolean',
    ];

    public function floatRecord()
    {
        return $this->belongsTo(FloatRecord::class);
    }
}
