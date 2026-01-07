<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SubscriptionPlan::orderBy('sort_order')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:subscription_plans,slug',
            'price' => 'numeric',
            'features' => 'array'
        ]);

        return SubscriptionPlan::create($validated);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update($request->all());
        return $subscriptionPlan;
    }
}
