<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = [
        'question',
        'description',
        'starts_at',
        'ends_at',
        'is_active',
        'allow_multiple_choice',
        'requires_login',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_multiple_choice' => 'boolean',
        'requires_login' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(PollOption::class)->orderBy('order');
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }
}
