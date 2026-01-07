<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::withCount('articles')
            ->with('parent')
            ->orderBy('order');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        return response()->json($query->paginate($request->query('per_page', 15)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sections,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:sections,id',
            'order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'icon' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $section = Section::create($validated);

        return response()->json($section, 201);
    }

    public function show(Section $section)
    {
        return response()->json($section->load('parent'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'slug' => 'nullable|string|max:255|unique:sections,slug,' . $section->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:sections,id',
            'order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'icon' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $section->update($validated);

        return response()->json($section);
    }

    public function destroy(Section $section)
    {
        if ($section->articles()->exists()) {
            return response()->json([
                'message' => 'Cannot delete section with associated articles.'
            ], 422);
        }

        if ($section->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete section with sub-sections.'
            ], 422);
        }

        $section->delete();

        return response()->json(null, 204);
    }
}
