<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsProvision extends Model
{
    protected $table = 'fs_provisions';

    protected $fillable = [
        'period_id',
        'provision_type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'double',
    ];

    public function period()
    {
        return $this->belongsTo(FinancialPeriod::class, 'period_id');
    }
}
