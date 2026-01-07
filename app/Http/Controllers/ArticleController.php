<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Services\CacheService;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::with(['section', 'mainAuthor'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->author_id, fn($q) => $q->where('author_id', $request->author_id))
            ->latest()
            ->paginate(15);

        return response()->json($articles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'section_id' => 'required|exists:sections,id',
            'series_id' => 'nullable|exists:series,id',
            'format' => 'required|in:standard,video,audio,gallery,live',
            'type' => 'required|in:news,opinion,editorial,feature,special_report,sport,lifestyle,video,live',
            'status' => 'required|in:draft,review,published,archived,scheduled',
            'published_at' => 'nullable|date',
            'scheduled_for' => 'nullable|date',
            'featured_image_url' => 'nullable|string',
            'featured_image_caption' => 'nullable|string',
            'featured_image_credit' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'video_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'poll_id' => 'nullable|exists:polls,id',
            'is_featured' => 'boolean',
            'is_exclusive' => 'boolean',
            'is_live' => 'boolean',
            'allow_comments' => 'boolean',
            'author_ids' => 'required|array',
            'author_ids.*' => 'exists:users,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'edition_ids' => 'nullable|array',
            'edition_ids.*' => 'exists:editions,id',
            'metadata' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $validated['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($validated['title']) . '-' . Str::random(5);
            if ($request->has('author_ids') && !empty($validated['author_ids'])) {
                $validated['author_id'] = $validated['author_ids'][0];
            }

            if ($validated['status'] === 'published' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }

            $article = Article::create($validated);

            // Sync Authors
            $authorsWithOrder = collect($validated['author_ids'])->mapWithKeys(fn($id, $index) => [$id => ['order' => $index]]);
            $article->authors()->sync($authorsWithOrder);

            // Sync Tags
            if (!empty($validated['tag_ids'])) {
                $article->tags()->sync($validated['tag_ids']);
            }

            // Sync Editions
            if (isset($validated['edition_ids'])) {
                $article->editions()->sync($validated['edition_ids']);
            }

            if ($article->status === 'published') {
                CacheService::clearPublicCaches();
            }

            return response()->json($article->load(['authors', 'tags', 'editions']), 201);
        });
    }

    public function show(Article $article)
    {
        return response()->json($article->load(['authors', 'tags', 'editions', 'section', 'series']));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'string',
            'section_id' => 'exists:sections,id',
            'series_id' => 'nullable|exists:series,id',
            'format' => 'in:standard,video,audio,gallery,live',
            'type' => 'in:news,opinion,editorial,feature,special_report,sport,lifestyle,video,live',
            'status' => 'in:draft,review,published,archived,scheduled',
            'published_at' => 'nullable|date',
            'scheduled_for' => 'nullable|date',
            'featured_image_url' => 'nullable|string',
            'featured_image_caption' => 'nullable|string',
            'featured_image_credit' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'video_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'poll_id' => 'nullable|exists:polls,id',
            'is_featured' => 'boolean',
            'is_exclusive' => 'boolean',
            'is_live' => 'boolean',
            'allow_comments' => 'boolean',
            'author_ids' => 'array',
            'tag_ids' => 'nullable|array',
            'edition_ids' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $article) {
            $wasPublished = $article->status === 'published';

            if (isset($validated['status']) && $validated['status'] === 'published' && empty($article->published_at)) {
                $validated['published_at'] = now();
            }

            $article->update($validated);

            if (isset($validated['author_ids']) && !empty($validated['author_ids'])) {
                $authorsWithOrder = collect($validated['author_ids'])->mapWithKeys(fn($id, $index) => [$id => ['order' => $index]]);
                $article->authors()->sync($authorsWithOrder);
                $article->update(['author_id' => $validated['author_ids'][0]]);
            }

            if (isset($validated['tag_ids'])) {
                $article->tags()->sync($validated['tag_ids']);
            }

            if (isset($validated['edition_ids'])) {
                $article->editions()->sync($validated['edition_ids']);
            }

            if ($article->status === 'published' || $wasPublished) {
                CacheService::clearPublicCaches();
            }

            return response()->json($article->fresh(['authors', 'tags', 'editions']));
        });
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(null, 204);
    }
}
