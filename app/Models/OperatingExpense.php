<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OperatingExpense extends Model
{
    protected $fillable = [
        'uuid',
        'taxonomy_item_id',
        'category',
        'title',
        'description',
        'amount',
        'receipt_path',
        'due_date',
        'payment_date',
        'status',
        'is_recurring',
        'recurring_period',
        'approved_by',
        'recorded_by',
        'treasury_account_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'is_recurring' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->recorded_by)) {
                $model->recorded_by = auth()->id();
            }
        });
    }

    public function taxonomyItem(): BelongsTo
    {
        return $this->belongsTo(ExpenseTaxonomyItem::class, 'taxonomy_item_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(TreasuryAccount::class, 'treasury_account_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
