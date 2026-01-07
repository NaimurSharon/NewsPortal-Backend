<?php

namespace App\Http\Controllers;

use App\Models\BreakingNews;
use App\Events\BreakingNewsPublished;
use Illuminate\Http\Request;

class BreakingNewsController extends Controller
{
    public function index()
    {
        return response()->json(BreakingNews::where('is_active', true)->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'link' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $news = BreakingNews::create([
            'title' => $request->title,
            'link' => $request->link,
            'is_active' => true,
            'expires_at' => $request->expires_at,
        ]);

        broadcast(new BreakingNewsPublished($news))->toOthers();

        return response()->json($news, 201);
    }

    public function destroy(BreakingNews $breakingNews)
    {
        $breakingNews->update(['is_active' => false]);
        return response()->json(null, 204);
    }
}
