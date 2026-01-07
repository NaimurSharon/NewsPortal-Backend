<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndex extends Model
{
    protected $table = 'search_index';

    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function searchable()
    {
        return $this->morphTo();
    }
}
