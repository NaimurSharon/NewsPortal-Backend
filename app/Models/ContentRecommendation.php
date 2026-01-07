<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'article_id',
        'score',
        'algorithm',
        'context',
        'shown_at',
        'clicked_at',
    ];

    protected $casts = [
        'context' => 'json',
        'shown_at' => 'datetime',
        'clicked_at' => 'datetime',
        'score' => 'decimal:4',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
