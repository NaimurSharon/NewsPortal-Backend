<?php

namespace App\Http\Controllers;

use App\Models\AdUnit;
use Illuminate\Http\Request;

class AdUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AdUnit::with(['campaign', 'placement'])->latest()->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'campaign_id' => 'required|exists:ad_campaigns,id',
            'placement_id' => 'required|exists:ad_placements,id',
            'media_id' => 'nullable|exists:media,id',
            'html_content' => 'nullable|string',
            'impressions_limit' => 'nullable|integer|min:1',
            'clicks_limit' => 'nullable|integer|min:1',
            'weight' => 'integer|min:1',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        return AdUnit::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(AdUnit $adUnit)
    {
        return $adUnit->load(['campaign', 'placement']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdUnit $adUnit)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'placement_id' => 'exists:ad_placements,id',
            'media_id' => 'nullable|exists:media,id',
            'html_content' => 'nullable|string',
            'impressions_limit' => 'nullable|integer',
            'clicks_limit' => 'nullable|integer',
            'weight' => 'integer|min:1',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $adUnit->update($validated);
        return $adUnit;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdUnit $adUnit)
    {
        $adUnit->delete();
        return response()->noContent();
    }
}
