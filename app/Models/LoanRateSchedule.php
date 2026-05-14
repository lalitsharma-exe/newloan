<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRateSchedule extends Model
{
    use HasFactory;

    protected $table = 'loan_rate_schedule';

    protected $fillable = [
        'rate_name', 'interest_rate', 'effective_date', 'expiry_date', 'approved_by'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'interest_rate' => 'decimal:4',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
