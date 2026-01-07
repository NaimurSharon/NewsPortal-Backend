<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'content',
        'type',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'json',
        'is_active' => 'boolean',
    ];
}
