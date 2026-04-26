<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBillPaydayEvent extends Model
{
    protected $table = 'mybill_payday_events';

    protected $fillable = [
        'user_id',
        'salary_amount',
        'detected_at',
        'loans_settled',
        'total_deducted',
        'trigger_type',
        'triggered_by',
        'notes',
    ];

    protected $casts = [
        'salary_amount'  => 'decimal:2',
        'total_deducted' => 'decimal:2',
        'detected_at'    => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function user()        { return $this->belongsTo(User::class); }
    public function triggeredBy() { return $this->belongsTo(User::class, 'triggered_by'); }
}
