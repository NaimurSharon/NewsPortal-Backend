<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyMetric extends Model
{
    protected $fillable = [
        'date',
        'metric_type',
        'entity_type',
        'entity_id',
        'value',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:4',
    ];
}
