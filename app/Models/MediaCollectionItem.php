<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaCollectionItem extends Model
{
    protected $fillable = [
        'collection_id',
        'media_id',
        'order',
        'caption',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function collection()
    {
        return $this->belongsTo(MediaCollection::class);
    }
}
