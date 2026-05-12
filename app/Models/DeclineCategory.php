<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeclineCategory extends Model
{
    protected $fillable = ['id', 'name', 'display_order', 'reasons'];
    
    protected $casts = [
        'reasons' => 'array',
    ];
}
