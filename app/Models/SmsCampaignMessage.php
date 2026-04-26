<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaignMessage extends Model
{
    protected $fillable = [
        'campaign_id', 'phone', 'recipient_name',
        'status', 'error', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(SmsCampaign::class, 'campaign_id');
    }
}
