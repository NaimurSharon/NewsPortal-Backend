<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Article;
use App\Models\Section;
use App\Models\Edition;

class ContentCacheService
{
    public const TTL_HOMEPAGE = 3600; // 1 hour (Phase 2)
    public const TTL_ARTICLE = 7200;  // 2 hours (Phase 2)
    public const TTL_MENU = 86400;   // 24 hours

    /**
     * Get or Set Homepage data
     */
    public function getHomepage(Edition $edition)
    {
        $key = "homepage.{$edition->code}";

        // Use tags if driver supports it (Redis), otherwise generic cache
        if (Cache::supportsTags()) {
            return Cache::tags(['homepage', 'content', "edition:{$edition->id}"])->remember($key, self::TTL_HOMEPAGE, function () use ($edition) {
                return $this->buildHomepage($edition);
            });
        }

        return Cache::remember($key, self::TTL_HOMEPAGE, function () use ($edition) {
            return $this->buildHomepage($edition);
        });
    }

    /**
     * Get or Set Article data
     */
    public function getArticle(string $slug)
    {
        return Cache::remember("article.{$slug}", self::TTL_ARTICLE, function () use ($slug) {
            return Article::where('slug', $slug)
                ->published()
                ->with(['authors', 'tags', 'section', 'series'])
                ->firstOrFail();
        });
    }

    /**
     * Clear generic content caches
     * Used statically by Models on update
     */
    public static function clearAllContentCache()
    {
        if (Cache::supportsTags()) {
            Cache::tags(['homepage', 'content'])->flush();
        } else {
            // If tags aren't supported (File/Database driver), we clear EVERYTHING 
            // to ensure news is immediate.
            Cache::flush();
        }

        // Also clear common lists
        Cache::forget('sections_list');
    }

    public function clearContentCache()
    {
        self::clearAllContentCache();
    }

    protected function buildHomepage(Edition $edition)
    {
        // 1. Hero / Top Stories - Optimized with selective columns
        $topStories = Article::published()
            ->whereHas('editions', fn($q) => $q->where('editions.id', $edition->id))
            ->where('is_featured', true)
            ->with([
                'section:id,name,slug',
                'mainAuthor:id,name,avatar_url',
            ])
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image_url',
                'featured_image_caption',
                'published_at',
                'section_id',
                'author_id',
                'is_featured',
                'is_exclusive',
                'is_live',
                'view_count',
                'reading_time',
                'format',
                'type'
            ])
            ->latest('published_at')
            ->take(5)
            ->get();

        // 2. Latest News - Optimized
        $latestNews = Article::published()
            ->whereHas('editions', fn($q) => $q->where('editions.id', $edition->id))
            ->with([
                'section:id,name,slug',
                'mainAuthor:id,name,avatar_url',
            ])
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image_url',
                'featured_image_caption',
                'published_at',
                'section_id',
                'author_id',
                'is_featured',
                'is_exclusive',
                'is_live',
                'view_count',
                'reading_time',
                'format',
                'type'
            ])
            ->latest('published_at')
            ->take(8)
            ->get();

        // 3. Sections Content (Dynamic) - Optimized with single query per section
        $featuredSections = Section::where('is_featured', true)
            ->orderBy('order')
            ->select('id', 'name', 'slug')
            ->get();

        $sectionsContent = [];

        foreach ($featuredSections as $section) {
            // Get child section IDs in one query
            $childIds = $section->children()->pluck('id')->toArray();
            $sectionIds = array_merge([$section->id], $childIds);

            $sectionsContent[] = [
                'id' => $section->id,
                'name' => $section->name,
                'slug' => $section->slug,
                'articles' => Article::published()
                    ->whereIn('section_id', $sectionIds)
                    ->whereHas('editions', fn($q) => $q->where('editions.id', $edition->id))
                    ->with([
                        'section:id,name,slug',
                        'mainAuthor:id,name,avatar_url',
                    ])
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'excerpt',
                        'featured_image_url',
                        'featured_image_caption',
                        'published_at',
                        'section_id',
                        'author_id',
                        'is_featured',
                        'is_exclusive',
                        'is_live',
                        'view_count',
                        'reading_time',
                        'format',
                        'type'
                    ])
                    ->latest('published_at')
                    ->take(4)
                    ->get()
            ];
        }

        return [
            'edition' => $edition,
            'hero' => $topStories,
            'latest' => $latestNews,
            'sections' => $sectionsContent,
        ];
    }
}
