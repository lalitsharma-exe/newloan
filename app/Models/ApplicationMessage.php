<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationMessage extends Model
{
    protected $fillable = [
        'application_id', 'sender_type', 'sender_id', 'message', 'read_at'
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplication::class);
    }
}
