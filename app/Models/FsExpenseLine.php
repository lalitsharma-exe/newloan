<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsExpenseLine extends Model
{
    protected $table = 'fs_expense_lines';

    protected $fillable = [
        'period_id',
        'expense_code',
        'expense_label',
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
