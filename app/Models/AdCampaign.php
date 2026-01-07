<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdCampaign extends Model
{
    protected $fillable = [
        'name',
        'advertiser_id',
        'type',
        'format',
        'target_url',
        'budget',
        'daily_budget',
        'start_date',
        'end_date',
        'status',
        'targeting',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'targeting' => 'json',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function units()
    {
        return $this->hasMany(AdUnit::class, 'campaign_id');
    }
}
