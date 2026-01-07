<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdUnit extends Model
{
    protected $fillable = [
        'name',
        'campaign_id',
        'placement_id',
        'media_id',
        'html_content',
        'impressions_limit',
        'clicks_limit',
        'weight',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class);
    }

    public function placement()
    {
        return $this->belongsTo(AdPlacement::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function stats()
    {
        return $this->hasMany(AdStat::class);
    }
}
