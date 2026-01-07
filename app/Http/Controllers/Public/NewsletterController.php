<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscriptions,email',
        ]);

        $subscription = NewsletterSubscription::create([
            'email' => $request->email,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Thank you for subscribing to our newsletter!',
            'subscription' => $subscription
        ], 201);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        NewsletterSubscription::where('email', $request->email)->update(['is_active' => false]);

        return response()->json([
            'message' => 'You have been successfully unsubscribed.'
        ]);
    }
}
