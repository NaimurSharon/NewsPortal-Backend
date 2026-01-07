<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledContent extends Model
{
    protected $table = 'scheduled_content';

    protected $fillable = [
        'article_id',
        'scheduled_for',
        'scheduled_by',
        'action',
        'status',
        'error_message',
        'executed_at'
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
