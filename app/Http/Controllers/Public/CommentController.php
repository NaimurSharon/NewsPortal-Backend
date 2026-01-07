<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Get comments for an article (Threaded)
     */
    public function index(Article $article)
    {
        // Get top-level comments with their children
        return $article->comments()
            ->whereNull('parent_id')
            ->where('status', 'approved') 
            ->with(['user:id,name,avatar_url', 'replies.user:id,name,avatar_url'])
            ->latest()
            ->get();
    }

    /**
     * Post a comment
     */
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = $article->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => 'approved',
        ]);

        return $comment->load('user');
    }

    /**
     * Delete own comment
     */
    public function destroy(Request $request, Comment $comment)
    {
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete(); // Soft delete if trait enabled, or hard delete
        return response()->noContent();
    }
}
