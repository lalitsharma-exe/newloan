<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseTaxonomyItem extends Model
{
    use HasFactory;

    protected $fillable = ['subcategory_id', 'name', 'ref_code'];

    public function subcategory()
    {
        return $this->belongsTo(ExpenseSubcategory::class, 'subcategory_id');
    }
}
