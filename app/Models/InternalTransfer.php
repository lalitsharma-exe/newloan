<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalTransfer extends Model
{
    protected $fillable = [
        'uuid',
        'transfer_type',
        'from_account_id',
        'to_account_id',
        'amount',
        'transfer_date',
        'bank_reference',
        'mpesa_confirmation',
        'initiated_by_user_id',
        'confirmed_by_user_id',
        'status',
        'proof_of_transfer',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($transfer) {
            $transfer->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function fromAccount() { return $this->belongsTo(TreasuryAccount::class, 'from_account_id'); }
    public function toAccount()   { return $this->belongsTo(TreasuryAccount::class, 'to_account_id'); }
    public function initiator()   { return $this->belongsTo(User::class, 'initiated_by_user_id'); }
    public function confirmer()   { return $this->belongsTo(User::class, 'confirmed_by_user_id'); }
}
