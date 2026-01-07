<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($section) {
            \App\Services\ContentCacheService::clearAllContentCache();
        });

        static::deleted(function ($section) {
            \App\Services\ContentCacheService::clearAllContentCache();
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'order',
        'is_active',
        'is_featured',
        'icon',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Section::class, 'parent_id')->orderBy('order');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function series()
    {
        return $this->hasMany(Series::class);
    }
}
