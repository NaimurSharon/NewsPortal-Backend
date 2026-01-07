<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * List available plans
     */
    public function plans()
    {
        return SubscriptionPlan::where('is_active', true)
            ->where('type', '!=', 'free')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create Checkout Session (Mock)
     */
    public function subscribe(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:subscription_plans,id']);
        
        $plan = SubscriptionPlan::find($request->plan_id);
        $user = auth()->user();

        // Normally we would call Stripe::checkout()->create(...)
        // For now, we return a mock URL
        
        return response()->json([
            'checkout_url' => "https://checkout.stripe.com/mock/{$plan->slug}?user={$user->id}",
            'message' => 'Redirect user to checkout_url'
        ]);
    }

    /**
     * Handle Stripe Webhook (Mock)
     */
    public function webhook(Request $request)
    {
        // Logic to verify signature
        // Logic to update UserSubscription table
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Get My Subscription
     */
    public function me(Request $request)
    {
        return $request->user()->subscriptions()->with('plan')->latest()->first();
    }
}
