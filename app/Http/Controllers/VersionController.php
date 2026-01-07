<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    /**
     * List versions of an article
     */
    public function index(Article $article)
    {
        return response()->json($article->content_versions ?? []);
    }

    /**
     * Save a checkpoint/version
     */
    public function store(Request $request, Article $article)
    {
        $versions = $article->content_versions ?? [];
        
        $newVersion = [
            'id' => uniqid(),
            'saved_at' => now()->toIso8601String(),
            'saved_by' => $request->user()->name,
            'title' => $article->title,
            'content' => $article->content,
            'excerpt' => $article->excerpt
        ];

        array_unshift($versions, $newVersion); // Add to top
        
        // Limit to last 20 versions
        $versions = array_slice($versions, 0, 20);

        $article->update(['content_versions' => $versions]);

        return response()->json($newVersion);
    }

    /**
     * Restore a version
     */
    public function restore(Request $request, Article $article, $versionId)
    {
        $versions = $article->content_versions ?? [];
        $target = collect($versions)->firstWhere('id', $versionId);

        if (!$target) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        // Save current as a backup before restoring? Yes.
        $this->store($request, $article);

        $article->update([
            'title' => $target['title'],
            'content' => $target['content'],
            'excerpt' => $target['excerpt']
        ]);

        return response()->json(['message' => 'Restored successfully']);
    }
}
