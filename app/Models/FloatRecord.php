<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FloatRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'principal_amount',
        'charge_amount',
        'outstanding_balance',
        'purpose',
        'affordability_result',
        'disposable_income',
        'applied_at',
        'approved_at',
        'disbursed_at',
        'due_date',
        'closed_at',
        'admin_notes',
        'penalty_count',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'closed_at' => 'datetime',
        'due_date' => 'date',
        'principal_amount' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'disposable_income' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penaltyLogs()
    {
        return $this->hasMany(FloatPenaltyLog::class);
    }
}
