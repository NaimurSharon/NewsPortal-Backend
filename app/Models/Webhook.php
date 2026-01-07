<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'name',
        'url',
        'events',
        'secret',
        'is_active',
        'last_triggered_at',
        'last_response_status',
        'last_response_body',
    ];

    protected $casts = [
        'events' => 'json',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];
}
