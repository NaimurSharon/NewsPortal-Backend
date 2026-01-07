<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    public $timestamps = false; // Custom created_at only

    protected $fillable = [
        'query',
        'user_id',
        'results_count',
        'filters',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'filters' => 'json',
        'created_at' => 'datetime',
    ];
}
