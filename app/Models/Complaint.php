<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'article_link',
        'type',
        'description',
        'status',
        'resolution_note',
        'ip_address'
    ];
}
