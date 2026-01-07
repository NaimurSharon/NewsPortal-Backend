<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdStat extends Model
{
    protected $fillable = [
        'ad_unit_id',
        'date',
        'impressions',
        'clicks',
        'conversions',
        'revenue',
    ];

    public function adUnit()
    {
        return $this->belongsTo(AdUnit::class);
    }
}
