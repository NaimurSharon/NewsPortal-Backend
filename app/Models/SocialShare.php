<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialShare extends Model
{
    public $timestamps = false; // Custom created_at only

    protected $fillable = [
        'article_id',
        'platform',
        'shared_by',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
