<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = ['user_id', 'preference_type', 'preference_value'];

    protected $casts = [
        'preference_value' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
