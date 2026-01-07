<?php

namespace App\Http\Controllers;

use App\Models\DailyMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get Daily Metrics (Auto-generated if missing)
     */
    public function index(Request $request)
    {
        $days = $request->input('days', 30);
        
        // 1. Try fetching from dedicated table
        $metrics = DailyMetric::where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get();
            
        if ($metrics->count() > 0) {
            return $metrics;
        }

        // 2. Fallback: Generate real-time report from primary tables
        $reportData = [];
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days);

        // Fetch aggregated data
        $articles = DB::table('articles')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date');

        $users = DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->pluck('count', 'date');
            
        // Check if comments table exists before querying
        $comments = [];
        if (DB::getSchemaBuilder()->hasTable('comments')) {
            $comments = DB::table('comments')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->pluck('count', 'date');
        }

        // Build continuous period
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            
            $reportData[] = [
                'date' => $date,
                'new_articles' => $articles[$date] ?? 0,
                'new_users' => $users[$date] ?? 0,
                'new_comments' => $comments[$date] ?? 0,
                'total_engagement' => ($comments[$date] ?? 0) // Simplified engagement
            ];
        }

        return response()->json($reportData);
    }

    /**
     * Generate Ad-hoc Report (Mock)
     */
    public function generate(Request $request)
    {
        // Provide CSV download link logic here
        return response()->json(['url' => 'https://api.newsportal.com/reports/download/123.csv']);
    }
}
