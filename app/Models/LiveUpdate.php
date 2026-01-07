<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'title',
        'content',
        'is_pinned',
        'author_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
