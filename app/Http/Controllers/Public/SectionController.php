<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Section;
use App\Models\Edition;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use App\Http\Resources\Public\ArticleResource;
use App\Http\Resources\Public\SectionResource;

class SectionController extends Controller
{
    public function show(Request $request, $slug)
    {
        $section = Section::where('slug', $slug)->with('children')->firstOrFail();
        
        $editionCode = $request->query('edition', 'international');
        $page = $request->query('page', 1);
        
        $cacheKey = "section_{$slug}_{$editionCode}_page_{$page}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($section, $editionCode) {
            $edition = Edition::where('code', $editionCode)->first();
            
            if (!$edition) {
                $edition = Edition::where('is_default', true)->first();
            }

            // Get articles for the main section
            $articles = Article::published()
                ->where(function($q) use ($section) {
                    $q->where('section_id', $section->id)
                      ->orWhereIn('section_id', $section->children->pluck('id'));
                })
                ->when($edition, function($q) use ($edition) {
                    $q->whereHas('editions', fn($eq) => $eq->where('editions.id', $edition->id));
                })
                ->with(['mainAuthor', 'section', 'authors'])
                ->latest('published_at')
                ->paginate(15);

            // Load articles for each subsection (children)
            $subsectionsWithArticles = $section->children->map(function($subsection) use ($edition) {
                $subsectionArticles = Article::published()
                    ->where('section_id', $subsection->id)
                    ->when($edition, function($q) use ($edition) {
                        $q->whereHas('editions', fn($eq) => $eq->where('editions.id', $edition->id));
                    })
                    ->with(['mainAuthor', 'section', 'authors'])
                    ->latest('published_at')
                    ->take(8)
                    ->get();

                return [
                    'id' => $subsection->id,
                    'name' => $subsection->name,
                    'slug' => $subsection->slug,
                    'description' => $subsection->description,
                    'articles' => ArticleResource::collection($subsectionArticles)
                ];
            });

            return [
                'section' => array_merge(
                    (new SectionResource($section))->resolve(),
                    ['children' => $subsectionsWithArticles]
                ),
                'articles' => \App\Http\Resources\Public\ArticleListResource::collection($articles)->response()->getData(true)
            ];
        });
    }

    public function editions()
    {
        return response()->json(Edition::all());
    }

    public function sections()
    {
        $sections = Section::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();
            
        return SectionResource::collection($sections);
    }
}
