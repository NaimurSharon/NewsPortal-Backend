<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\Request;

use App\Http\Resources\Public\ArticleResource;
use App\Http\Resources\Public\LiveUpdateResource;

class ArticleController extends Controller
{
    public function show(Request $request, $slug)
    {
        $article = Article::where('slug', $slug)
            ->with(['authors', 'section', 'series', 'tags', 'editions', 'mainAuthor', 'poll'])
            ->firstOrFail();

        if ($article->status !== 'published' && !$request->user()?->is_staff) {
            abort(404);
        }

        // Increment view count (Simple version)
        $article->increment('view_count');

        // Track detailed view (Wrapped in try-catch to prevent page crash on logger error)
        try {
            ArticleView::create([
                'article_id' => $article->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to track article view: " . $e->getMessage());
        }

        // If live, load latest updates
        $liveUpdates = null;
        if ($article->format === 'live') {
            $liveUpdates = $article->liveUpdates()->with('author')->latest()->take(20)->get();
        }

        // Related articles (Same section)
        $related = Article::published()
            ->where('section_id', $article->section_id)
            ->where('id', '!=', $article->id)
            ->with(['section'])
            ->latest('published_at')
            ->take(10)
            ->get();

        // Latest articles (Newly posted news)
        $latest = Article::published()
            ->where('id', '!=', $article->id)
            ->with(['section'])
            ->latest('published_at')
            ->take(6)
            ->get();

        return response()->json([
            'article' => new ArticleResource($article),
            'live_updates' => $liveUpdates ? LiveUpdateResource::collection($liveUpdates) : null,
            'related' => \App\Http\Resources\Public\ArticleListResource::collection($related),
            'latest' => \App\Http\Resources\Public\ArticleListResource::collection($latest)
        ]);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:3']);
        $query = $request->q;

        $articles = Article::published()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('subtitle', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['mainAuthor', 'section'])
            ->latest('published_at')
            ->paginate(15);

        return [
            'articles' => \App\Http\Resources\Public\ArticleListResource::collection($articles)->response()->getData(true)
        ];
    }

    public function liveUpdates($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        if ($article->format !== 'live' && !$article->is_live) {
            return response()->json(['message' => 'Not a live article'], 400);
        }

        $updates = $article->liveUpdates()->with('author')->latest()->get();
        return LiveUpdateResource::collection($updates);
    }
}
