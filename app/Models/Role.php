<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'description', 'permissions', 'is_system'];

    protected $casts = [
        'permissions' => 'json',
        'is_system' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
