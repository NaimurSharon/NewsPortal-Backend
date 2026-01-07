<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lightweight Article Resource for List Views
 * Excludes heavy fields like 'content' to reduce payload size
 */
class ArticleListResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'published_at' => $this->published_at,
            'reading_time' => $this->reading_time,
            'featured_image_url' => $this->featured_image_url,
            'featured_image_caption' => $this->featured_image_caption,
            'is_featured' => $this->is_featured,
            'is_exclusive' => $this->is_exclusive,
            'is_live' => $this->is_live,
            'view_count' => $this->view_count,
            'format' => $this->format,
            'type' => $this->type,

            // Optimized nested resources - only essential fields
            'section' => $this->when($this->relationLoaded('section') && $this->section, function () {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name,
                    'slug' => $this->section->slug,
                ];
            }),

            'main_author' => $this->when($this->relationLoaded('mainAuthor') && $this->mainAuthor, function () {
                return [
                    'id' => $this->mainAuthor->id,
                    'name' => $this->mainAuthor->name,
                    'avatar_url' => $this->mainAuthor->avatar_url ?? null,
                ];
            }),

            // Only include authors array if explicitly loaded and needed
            'authors' => $this->when(
                $this->relationLoaded('authors') && $request->input('include_authors'),
                function () {
                    return $this->authors->map(fn($author) => [
                        'id' => $author->id,
                        'name' => $author->name,
                        'avatar_url' => $author->avatar_url ?? null,
                    ]);
                }
            ),
        ];
    }
}
