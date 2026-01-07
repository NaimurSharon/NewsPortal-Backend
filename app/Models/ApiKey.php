<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key',
        'secret',
        'user_id',
        'permissions',
        'rate_limit',
        'expires_at',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'json',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
