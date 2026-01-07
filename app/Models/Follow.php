<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    public $timestamps = false; // Custom created_at only

    protected $fillable = [
        'user_id',
        'followable_type',
        'followable_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function followable()
    {
        return $this->morphTo();
    }
}
