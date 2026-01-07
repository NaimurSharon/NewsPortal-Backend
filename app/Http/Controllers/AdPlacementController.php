<?php

namespace App\Http\Controllers;

use App\Models\AdPlacement;
use Illuminate\Http\Request;

class AdPlacementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AdPlacement::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:ad_placements,code',
            'type' => 'required|in:header,sidebar,footer,inline,popup,sticky',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'max_ads' => 'integer|min:1',
            'priority' => 'integer',
            'is_active' => 'boolean'
        ]);

        return AdPlacement::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(AdPlacement $adPlacement)
    {
        return $adPlacement;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdPlacement $adPlacement)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|unique:ad_placements,code,' . $adPlacement->id,
            'type' => 'in:header,sidebar,footer,inline,popup,sticky',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'max_ads' => 'integer|min:1',
            'priority' => 'integer',
            'is_active' => 'boolean'
        ]);

        $adPlacement->update($validated);
        return $adPlacement;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdPlacement $adPlacement)
    {
        $adPlacement->delete();
        return response()->noContent();
    }
}
