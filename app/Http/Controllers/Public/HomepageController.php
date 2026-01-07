<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Edition;
use App\Models\Section;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;

use App\Http\Resources\Public\ArticleResource;
use App\Services\ContentCacheService;

class HomepageController extends Controller
{
    public function index(Request $request, ContentCacheService $cacheService, $editionCode = 'international')
    {
        $edition = Edition::where('code', $editionCode)->first()
                   ?? Edition::where('is_default', true)->first()
                   ?? Edition::first();

        if (!$edition) {
            return response()->json([
                'message' => 'No editions found. Please ensure the database is seeded.',
                'hero' => [],
                'latest' => [],
                'sections' => []
            ], 404);
        }

        $data = $cacheService->getHomepage($edition);

        return [
            'edition' => $data['edition'],
            'hero' => \App\Http\Resources\Public\ArticleListResource::collection($data['hero']),
            'latest' => \App\Http\Resources\Public\ArticleListResource::collection($data['latest']),
            'sections' => collect($data['sections'])->map(fn($section) => [
                'id' => $section['id'],
                'name' => $section['name'],
                'slug' => $section['slug'],
                'articles' => \App\Http\Resources\Public\ArticleListResource::collection($section['articles'])
            ]),
        ];
    }
}
