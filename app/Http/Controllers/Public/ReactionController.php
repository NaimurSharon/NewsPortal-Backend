<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    /**
     * React to an article (Toggle)
     */
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'type' => 'required|in:like,love,haha,insightful,wow,sad,angry'
        ]);

        $user = $request->user();

        // Check if exists
        $existing = $article->reactions()
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->type === $validated['type']) {
                // Toggle off if clicking same reaction
                $existing->delete();
                return response()->json(['status' => 'removed']);
            }
            // Update to new type
            $existing->update(['type' => $validated['type']]);
            return response()->json(['status' => 'updated', 'reaction' => $existing]);
        }

        // Create new
        $reaction = $article->reactions()->create([
            'user_id' => $user->id,
            'type' => $validated['type']
        ]);

        return response()->json(['status' => 'created', 'reaction' => $reaction]);
    }

    /**
     * Get aggregate stats for an article
     */
    public function stats(Request $request, Article $article)
    {
        $stats = $article->reactions()
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        $user = $request->user('sanctum');
        
        $userReactions = $user 
            ? $article->reactions()->where('user_id', $user->id)->pluck('type')->toArray()
            : [];

        $formatted = $stats->map(function($stat) use ($userReactions) {
            return [
                'type' => $stat->type,
                'count' => $stat->count,
                'user_reacted' => in_array($stat->type, $userReactions)
            ];
        });

        return response()->json(['stats' => $formatted]);
    }
}
