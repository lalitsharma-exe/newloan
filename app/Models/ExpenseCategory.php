<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ref_code'];

    public function subcategories()
    {
        return $this->hasMany(ExpenseSubcategory::class, 'category_id');
    }
}
