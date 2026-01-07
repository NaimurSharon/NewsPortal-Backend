<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::saved(function ($article) {
            \App\Services\ContentCacheService::clearAllContentCache();
        });

        static::deleted(function ($article) {
            \App\Services\ContentCacheService::clearAllContentCache();
        });

        static::restored(function ($article) {
            \App\Services\ContentCacheService::clearAllContentCache();
        });
    }

    protected $fillable = [
        'title',
        'subtitle',
        'slug',
        'excerpt',
        'content',
        'author_id',
        'section_id',
        'series_id',
        'format',
        'type',
        'status',
        'published_at',
        'scheduled_for',
        'reading_time',
        'featured_image_url',
        'featured_image_caption',
        'featured_image_credit',
        'gallery_images',
        'video_url',
        'audio_url',
        'is_featured',
        'is_exclusive',
        'is_live',
        'allow_comments',
        'view_count',
        'share_count',
        'metadata',
        'poll_id',
    ];

    protected $casts = [
        'gallery_images' => 'json',
        'metadata' => 'json',
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'is_featured' => 'boolean',
        'is_exclusive' => 'boolean',
        'is_live' => 'boolean',
        'allow_comments' => 'boolean',
    ];

    public function mainAuthor()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function authors()
    {
        return $this->belongsToMany(User::class, 'article_author')->withPivot('order')->orderBy('order');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag')->withPivot('relevance_score')->withTimestamps();
    }

    public function editions()
    {
        return $this->belongsToMany(Edition::class, 'article_edition')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function liveUpdates()
    {
        return $this->hasMany(LiveUpdate::class)->latest();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function workflow()
    {
        return $this->hasMany(ArticleWorkflow::class);
    }

    public function scheduledContent()
    {
        return $this->hasOne(ScheduledContent::class);
    }

    public function adCampaigns()
    {
        // Articles might target specific campaigns if native
        return $this->belongsToMany(AdCampaign::class, 'ad_units'); 
    }

    public function analytics()
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
}
