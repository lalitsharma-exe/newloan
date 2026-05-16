<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseSubcategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'ref_code'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(ExpenseTaxonomyItem::class, 'subcategory_id');
    }
}
