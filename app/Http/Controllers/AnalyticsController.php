<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Record an event (Public)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string', // pageview, click, scroll
            'url' => 'required|string',
            'meta' => 'nullable|array'
        ]);

        // In production, dispatch to Queue/Redis
        AnalyticsEvent::create([
            'event_type' => $validated['event_type'],
            'url' => $validated['url'],
            'metadata' => $validated['meta'] ?? [],
            'user_id' => $request->user('sanctum')?->id,
            'session_id' => $request->session()->getId(), // or header
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['status' => 'recorded']);
    }

    /**
     * Dashboard Stats (Admin)
     */
    public function dashboard(Request $request)
    {
        $stats = [
            'overview' => [
                'total_articles' => \App\Models\Article::count(),
                'published_articles' => \App\Models\Article::where('status', 'published')->count(),
                'draft_articles' => \App\Models\Article::where('status', 'draft')->count(),
                'review_articles' => \App\Models\Article::where('status', 'review')->count(),
                'scheduled_articles' => \App\Models\ScheduledContent::where('status', 'pending')->count(),
                'total_views' => \App\Models\Article::sum('view_count'),
                'total_comments' => \App\Models\Comment::count(),
                'total_users' => \App\Models\User::count(),
                'new_users_today' => \App\Models\User::whereDate('created_at', today())->count(),
                'newsletter_subscribers' => \App\Models\NewsletterSubscription::where('is_active', true)->count(),
                'pending_contributions' => \App\Models\Contribution::where('status', 'pending')->count(),
            ],
            'recent_activity' => \App\Models\AuditLog::with('user')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user' => $log->user?->name ?? 'System',
                        'action' => $log->action,
                        'type' => class_basename($log->auditable_type),
                        'label' => $this->getLogLabel($log),
                        'time' => $log->created_at->diffForHumans(),
                    ];
                }),
            'workflow_distribution' => [
                'draft' => \App\Models\Article::where('status', 'draft')->count(),
                'review' => \App\Models\Article::where('status', 'review')->count(),
                'scheduled' => \App\Models\ScheduledContent::where('status', 'pending')->count(),
                'published' => \App\Models\Article::where('status', 'published')->count(),
            ]
        ];

        return response()->json($stats);
    }

    private function getLogLabel($log)
    {
        if ($log->auditable_type === \App\Models\Article::class) {
            $article = \App\Models\Article::find($log->auditable_id);
            return $article?->title ?? "Article #{$log->auditable_id}";
        }
        
        return "{$log->action} on " . class_basename($log->auditable_type);
    }
}
