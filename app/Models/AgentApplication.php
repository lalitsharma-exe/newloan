<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_ref',
        'first_name',
        'last_name',
        'national_id',
        'mobile_number',
        'agent_type',
        'shop_name',
        'shop_location',
        'business_type',
        'national_id_path',
        'selfie_holding_id_path',
        'business_licence_path',
        'payout_method',
        'payout_number_or_details',
        'payout_account_name',
        'payout_bank_name',
        'status',
        'admin_feedback',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Boot function to generate application reference.
     */
    protected static function booted()
    {
        static::creating(function ($app) {
            if (empty($app->application_ref)) {
                $app->application_ref = self::generateApplicationReference();
            }
        });
    }

    public static function generateApplicationReference(): string
    {
        $latest = self::orderBy('id', 'desc')->first();
        if (!$latest) {
            return 'AGT-APP-0001';
        }

        $num = (int) str_replace('AGT-APP-', '', $latest->application_ref);
        return 'AGT-APP-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }
}
