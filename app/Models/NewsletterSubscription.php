<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    /** @use HasFactory<\Database\Factories\NewsletterSubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'is_active',
        'status',
        'user_id',
        'newsletter_id',
    ];
}
