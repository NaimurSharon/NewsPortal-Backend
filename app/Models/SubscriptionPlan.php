<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'price',
        'currency',
        'trial_days',
        'features',
        'limitations',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'json',
        'limitations' => 'json',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}
