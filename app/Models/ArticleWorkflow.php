<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleWorkflow extends Model
{
    protected $table = 'article_workflow';

    protected $fillable = [
        'article_id',
        'step_id',
        'assigned_to',
        'completed_by',
        'status',
        'notes',
        'deadline',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
