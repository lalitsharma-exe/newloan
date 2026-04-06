<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'requested_details', 'admin_note', 'status'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
