<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public static $wrap = null;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'format' => $this->format,
            'type' => $this->type,
            'published_at' => $this->published_at,
            'reading_time' => $this->reading_time,
            'featured_image_url' => $this->featured_image_url,
            'featured_image_caption' => $this->featured_image_caption,
            'featured_image_credit' => $this->featured_image_credit,
            'gallery_images' => $this->gallery_images,
            'video_url' => $this->video_url,
            'audio_url' => $this->audio_url,
            'is_featured' => $this->is_featured,
            'is_exclusive' => $this->is_exclusive,
            'is_live' => $this->is_live,
            'allow_comments' => $this->allow_comments,
            'view_count' => $this->view_count,
            'section' => new SectionResource($this->whenLoaded('section')),
            'authors' => UserResource::collection($this->whenLoaded('authors')),
            'main_author' => new UserResource($this->whenLoaded('mainAuthor')),
            'tags' => $this->whenLoaded('tags'),
            'series' => $this->whenLoaded('series'),
            'poll' => $this->whenLoaded('poll', function () use ($request) {
                if (!$this->poll)
                    return null;

                $hasVoted = \App\Models\PollVote::where('poll_id', $this->poll->id)
                    ->where(function ($q) use ($request) {
                        $q->where('ip_address', $request->ip());
                        if ($request->user('sanctum')) {
                            $q->orWhere('user_id', $request->user('sanctum')->id);
                        }
                    })->exists();
                return [
                    'id' => $this->poll->id,
                    'question' => $this->poll->question,
                    'options' => $this->poll->options,
                    'is_active' => $this->poll->is_active,
                    'user_has_voted' => $hasVoted,
                ];
            }),
            'metadata' => $this->metadata,
        ];
    }
}
