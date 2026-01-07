<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'event_type',
        'event_name',
        'page_url',
        'referrer_url',
        'article_id',
        'section_id',
        'duration',
        'scroll_depth',
        'device_info',
        'location_info',
        'metadata',
    ];

    protected $casts = [
        'device_info' => 'json',
        'location_info' => 'json',
        'metadata' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
