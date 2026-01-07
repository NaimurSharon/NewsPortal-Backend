<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Models\AdUnit;
use Illuminate\Http\Request;

class AdCampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AdCampaign::with(['advertiser', 'units.media'])
            ->withSum('units as total_impressions', 'impressions_limit') // This is limit
            ->withSum('units as total_clicks', 'clicks_limit') // This is limit
            // To get actual stats, we'd ideally have a hasManyThrough or similar.
            // For now, let's include the units with their own counts.
            ->latest()
            ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'advertiser_id' => 'required|exists:advertisers,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'targeting' => 'nullable|array',
            'target_url' => 'required|url',
            'type' => 'required|in:display,video,native,sponsored_content',
            'format' => 'required|string',
            'status' => 'required|in:draft,pending,active,paused,completed,cancelled'
        ]);

        $campaign = AdCampaign::create($validated + ['created_by' => auth()->id()]);
        return $campaign;
    }

    /**
     * Display the specified resource.
     */
    public function show(AdCampaign $adCampaign)
    {
        return $adCampaign->load(['advertiser', 'units']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdCampaign $adCampaign)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'advertiser_id' => 'exists:advertisers,id',
            'start_date' => 'date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric',
            'targeting' => 'nullable|array',
            'target_url' => 'url',
            'type' => 'in:display,video,native,sponsored_content',
            'format' => 'string',
            'status' => 'in:draft,pending,active,paused,completed,cancelled'
        ]);

        if ($request->has('status') && $request->status === 'active' && $adCampaign->status !== 'active') {
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        }

        $adCampaign->update($validated);
        return $adCampaign;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdCampaign $adCampaign)
    {
        $adCampaign->delete();
        return response()->noContent();
    }

    /**
     * Add a Unit to a Campaign
     */
    public function storeUnit(Request $request, AdCampaign $adCampaign)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'placement_id' => 'required|exists:ad_placements,id',
            'html_content' => 'required_without:media_id|nullable|string',
            'media_id' => 'required_without:html_content|nullable|exists:media,id',
            'impressions_limit' => 'nullable|integer|min:0',
            'clicks_limit' => 'nullable|integer|min:0',
            'weight' => 'integer|min:1|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        return $adCampaign->units()->create($validated);
    }
}
