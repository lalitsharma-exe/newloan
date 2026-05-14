<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'loan_id', 'complaint_date', 'complaint_type', 
        'description', 'resolution', 'status', 'cbl_reference', 'resolved_date'
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
