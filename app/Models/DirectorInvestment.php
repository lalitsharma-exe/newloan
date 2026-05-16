<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectorInvestment extends Model
{
    protected $fillable = [
        'amount_invested',
        'investment_date',
        'monthly_rate',
        'interest_accrued_to_date',
        'interest_paid_to_date',
        'principal_repaid',
        'status',
        'notes',
        'payout_year',
    ];

    protected $casts = [
        'investment_date' => 'date',
    ];

    public function getOutstandingPrincipalAttribute()
    {
        return $this->amount_invested - $this->principal_repaid;
    }
}
