<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'path',
        'url',
        'mime_type',
        'size',
        'type',
        'caption',
        'credit',
        'dimensions',
        'variants',
        'uploaded_by',
    ];

    protected $casts = [
        'dimensions' => 'json',
        'variants' => 'json',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
