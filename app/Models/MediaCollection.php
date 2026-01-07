<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaCollection extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'cover_media_id',
        'created_by',
        'order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->belongsToMany(Media::class, 'media_collection_items')
                    ->withPivot(['order', 'caption', 'metadata'])
                    ->orderBy('order');
    }
}
