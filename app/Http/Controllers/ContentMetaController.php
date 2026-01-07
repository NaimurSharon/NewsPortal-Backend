<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Edition;
use App\Models\Tag;
use App\Models\Series;
use App\Models\User;
use Illuminate\Http\Request;

class ContentMetaController extends Controller
{
    public function index()
    {
        // Ensure Video category exists
        $videoSection = Section::firstOrCreate(
            ['slug' => 'videos'],
            ['name' => 'Videos', 'order' => 99, 'is_active' => true]
        );

        return response()->json([
            'sections' => Section::orderBy('name')->get(),
            'editions' => Edition::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'series' => Series::where('is_active', true)->orderBy('title')->get(),
            'authors' => User::whereIn('role', ['admin', 'editor', 'reporter', 'journalist'])->orderBy('name')->get(),
            'polls' => \App\Models\Poll::where('is_active', true)->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
