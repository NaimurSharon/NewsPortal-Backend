<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'usage_count',
        'is_trending',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag')
                    ->withPivot('relevance_score')
                    ->withTimestamps();
    }
}
