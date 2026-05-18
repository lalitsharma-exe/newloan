<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'investor_type',
        'full_name',
        'id_number',
        'phone',
        'email',
        'address',
        'bank_name',
        'account_number',
        'status',
    ];

    /**
     * Get the user that owns the investor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investments for this investor.
     */
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }
}
