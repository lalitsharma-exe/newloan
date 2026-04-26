<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    protected $fillable = [
        'name', 'message', 'status', 'audience',
        'total_recipients', 'sent_count', 'failed_count',
        'created_by', 'queued_at', 'completed_at',
    ];

    protected $casts = [
        'queued_at'    => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(SmsCampaignMessage::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProgressAttribute(): int
    {
        if ($this->total_recipients <= 0) return 0;
        return (int) round(($this->sent_count + $this->failed_count) / $this->total_recipients * 100);
    }
}
