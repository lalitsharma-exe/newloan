<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DpdSnapshot extends Model
{
    use HasFactory;

    protected $table = 'dpd_snapshot';

    protected $fillable = ['loan_id', 'snapshot_date', 'dpd', 'aging_bucket'];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
