<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FsPpeRegister extends Model
{
    protected $table = 'fs_ppe_register';

    protected $fillable = [
        'asset_name',
        'asset_class',
        'purchase_date',
        'cost',
        'useful_life_years',
        'accumulated_dep',
        'net_book_value',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'cost'          => 'double',
        'accumulated_dep'=> 'double',
        'net_book_value'=> 'double',
    ];

    /**
     * Calculate sum of monthly/annual depreciation for a given year.
     */
    public static function annualDepreciation(int $year): float
    {
        return (float) self::where('status', 'active')
            ->whereYear('purchase_date', '<=', $year)
            ->get()
            ->sum(function ($asset) {
                return $asset->cost / $asset->useful_life_years;
            });
    }

    /**
     * Calculate cost of newly added assets purchased in a given year.
     */
    public static function annualAdditions(int $year): float
    {
        return (float) self::whereYear('purchase_date', $year)->sum('cost');
    }
}
