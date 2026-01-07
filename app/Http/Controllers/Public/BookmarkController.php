<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use App\Http\Resources\Public\ArticleResource;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = $request->user()->bookmarks()
            ->with(['article.section', 'article.mainAuthor'])
            ->latest()
            ->paginate(20);

        return ArticleResource::collection($bookmarks->pluck('article'));
    }

    public function store(Request $request, Article $article)
    {
        $user = $request->user();

        $existing = Bookmark::where('user_id', $user->id)
            ->where('article_id', $article->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'removed']);
        }

        $bookmark = Bookmark::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'folder' => $request->folder ?? 'Default',
            'notes' => $request->notes
        ]);

        return response()->json(['status' => 'bookmarked', 'bookmark' => $bookmark]);
    }

    public function check(Request $request, Article $article)
    {
        $isBookmarked = Bookmark::where('user_id', $request->user()->id)
            ->where('article_id', $article->id)
            ->exists();

        return response()->json(['is_bookmarked' => $isBookmarked]);
    }
}
