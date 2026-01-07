<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\LiveUpdate;
use Illuminate\Http\Request;

use App\Events\LiveUpdateCreated;

class LiveUpdateController extends Controller
{
    public function index(Article $article)
    {
        return response()->json($article->liveUpdates()->with('author')->paginate(30));
    }

    public function store(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'is_pinned' => 'boolean',
        ]);

        if ($article->format !== 'live') {
            return response()->json(['message' => 'This article is not a live format article.'], 400);
        }

        $update = $article->liveUpdates()->create([
            'title' => $request->title,
            'content' => $request->content,
            'is_pinned' => $request->is_pinned ?? false,
            'author_id' => $request->user()->id,
        ]);

        try {
            broadcast(new LiveUpdateCreated($update))->toOthers();
        } catch (\Exception $e) {
            \Log::error("Broadcasting failed for live update: " . $e->getMessage());
        }

        return response()->json($update, 201);
    }

    public function update(Request $request, LiveUpdate $liveUpdate)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'string',
            'is_pinned' => 'boolean',
        ]);

        $liveUpdate->update($request->only(['title', 'content', 'is_pinned']));

        return response()->json($liveUpdate);
    }

    public function destroy(LiveUpdate $liveUpdate)
    {
        $liveUpdate->delete();
        return response()->json(null, 204);
    }
}
