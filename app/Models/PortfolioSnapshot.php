<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioSnapshot extends Model
{
    protected $table = 'portfolio_snapshots';

    protected $fillable = [
        'snapshot_date',
        'active_loan_count',
        'gross_loan_book',
        'disbursed_count',
        'disbursed_amount',
        'settled_count',
        'settled_amount',
        'written_off_count',
        'written_off_amount',
        'par_30_amount',
        'par_30_rate',
        'par_90_amount',
        'par_90_rate',
        'provision_balance',
    ];

    protected $casts = [
        'snapshot_date'       => 'date',
        'active_loan_count'   => 'integer',
        'gross_loan_book'     => 'double',
        'disbursed_count'     => 'integer',
        'disbursed_amount'    => 'double',
        'settled_count'       => 'integer',
        'settled_amount'      => 'double',
        'written_off_count'   => 'integer',
        'written_off_amount'  => 'double',
        'par_30_amount'       => 'double',
        'par_30_rate'         => 'double',
        'par_90_amount'       => 'double',
        'par_90_rate'         => 'double',
        'provision_balance'   => 'double',
    ];
}
