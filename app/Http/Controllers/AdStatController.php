<?php

namespace App\Http\Controllers;

use App\Models\AdStat;
use App\Models\AdCampaign;
use App\Models\AdPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdStatController extends Controller
{
    public function index(Request $request)
    {
        $query = AdStat::with(['adUnit.campaign', 'adUnit.placement']);

        if ($request->has('campaign_id')) {
            $query->whereHas('adUnit', function($q) use ($request) {
                $q->where('campaign_id', $request->campaign_id);
            });
        }

        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        return $query->latest('date')->paginate(50);
    }

    public function summary()
    {
        $stats = AdStat::selectRaw('SUM(impressions) as total_impressions, SUM(clicks) as total_clicks')
            ->first();

        $activePlacements = AdPlacement::where('is_active', true)->count();
        $totalCampaigns = AdCampaign::count();

        $ctr = $stats->total_impressions > 0 
            ? ($stats->total_clicks / $stats->total_impressions) * 100 
            : 0;

        return [
            'totalImpressions' => number_format($stats->total_impressions ?? 0),
            'totalClicks' => number_format($stats->total_clicks ?? 0),
            'avgCtr' => number_format($ctr, 2) . '%',
            'activePlacements' => (string)$activePlacements,
            'totalCampaigns' => (string)$totalCampaigns,
            // Mock deltas for now as we don't have historical snapshots easily
            'impressionsDelta' => '+0%',
            'clicksDelta' => '+0%',
            'ctrDelta' => '+0%',
        ];
    }

    public function placementPerformance()
    {
        return AdStat::join('ad_units', 'ad_stats.ad_unit_id', '=', 'ad_units.id')
            ->join('ad_placements', 'ad_units.placement_id', '=', 'ad_placements.id')
            ->select(
                'ad_placements.name',
                DB::raw('SUM(ad_stats.impressions) as total_impressions'),
                DB::raw('SUM(ad_stats.clicks) as total_clicks'),
                DB::raw('(SUM(ad_stats.clicks) / NULLIF(SUM(ad_stats.impressions), 0)) * 100 as ctr')
            )
            ->groupBy('ad_placements.id', 'ad_placements.name')
            ->get();
    }
}
