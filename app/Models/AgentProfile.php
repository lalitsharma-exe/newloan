<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'agent_type',
        'shop_name',
        'shop_location',
        'business_type',
        'payout_method',
        'payout_number_or_details',
        'payout_account_name',
        'payout_bank_name',
        'total_earned',
        'pending_earnings',
        'contract_ref',
        'signed_at',
        'signature_base64_path',
        'signed_ip',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'total_earned' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateAgentID(): string
    {
        $latest = self::orderBy('id', 'desc')->first();
        if (!$latest) {
            $num = 1;
        } else {
            $num = (int) str_replace('AGT-', '', $latest->agent_id) + 1;
        }

        while (self::where('agent_id', 'AGT-' . str_pad($num, 4, '0', STR_PAD_LEFT))->exists()) {
            $num++;
        }

        return 'AGT-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
