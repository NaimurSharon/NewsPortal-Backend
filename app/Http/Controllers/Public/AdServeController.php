<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use App\Models\AdStat;
use App\Models\AdUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdServeController extends Controller
{
    /**
     * Serve an Ad Unit for a specific placement
     * GET /api/v1/ads/serve?placement=header_banner
     */
    public function serve(Request $request)
    {
        try {
            $code = $request->input('placement');

            if (!$code) {
                return response()->json(['error' => 'Placement code required'], 400);
            }

            $placement = AdPlacement::where('code', $code)->where('is_active', true)->first();

            if (!$placement) {
                // Return 204 No Content instead of 404 to avoid frontend console errors
                return response()->noContent();
            }

            // Simple query to find a valid ad unit
            $unit = AdUnit::where('placement_id', $placement->id)
                ->where('is_active', true)
                ->whereHas('campaign', function ($q) {
                    $q->where('status', 'active');
                })
                ->with(['media', 'campaign'])
                ->inRandomOrder()
                ->first();

            if (!$unit) {
                return response()->noContent();
            }

            // Construct HTML content
            $html = $unit->html_content;
            if (empty($html) && $unit->media) {
                $mediaUrl = $unit->media->url;
                $imgUrl = (str_starts_with($mediaUrl, 'http') || str_starts_with($mediaUrl, '//'))
                    ? $mediaUrl
                    : asset($mediaUrl);

                $html = "<img src=\"{$imgUrl}\" style=\"width:100%;height:100%;object-fit:cover;\" alt=\"Ad\" />";
            }

            // Ensure we have something to show
            if (empty($html)) {
                return response()->noContent();
            }

            return response()->json([
                'id' => $unit->id,
                'html' => $html,
                'tracking_pixel' => route('api.ads.impression', ['unit' => $unit->id]),
                'click_url' => route('api.ads.click', ['unit' => $unit->id]),
            ]);

        } catch (\Exception $e) {
            \Log::error("Ad Serve Error [{$request->input('placement')}]: " . $e->getMessage());
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record Impression
     * GET /api/v1/ads/impression/{unit}
     */
    public function impression(Request $request, $unitId)
    {
        // Fire and forget - use DB for now, ideally Redis queue
        AdStat::updateOrCreate(
            ['ad_unit_id' => $unitId, 'date' => now()->toDateString()],
            ['impressions' => DB::raw('impressions + 1')]
        );

        // Return 1x1 transparent gif
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif');
    }

    /**
     * Record Click and Redirect
     * GET /api/v1/ads/click/{unit}
     */
    public function click(Request $request, $unitId)
    {
        $unit = AdUnit::with('campaign')->find($unitId);

        if ($unit) {
            AdStat::updateOrCreate(
                ['ad_unit_id' => $unitId, 'date' => now()->toDateString()],
                ['clicks' => DB::raw('clicks + 1')]
            );

            if ($unit->campaign->target_url) {
                return redirect()->away($unit->campaign->target_url);
            }
        }

        return redirect('/');
    }
}
