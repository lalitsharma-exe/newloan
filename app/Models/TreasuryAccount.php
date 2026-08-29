<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreasuryAccount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'institution',
        'account_number',
        'balance',
        'currency',
        'is_active',
        'is_director_owned'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_director_owned' => 'boolean'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(TreasuryTransaction::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(OperatingExpense::class);
    }
}
