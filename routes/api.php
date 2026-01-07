<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Newsportal API v1
Route::prefix('v1')->middleware('throttle:1000,1')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Admin Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Media
        Route::get('/admin/media', [App\Http\Controllers\MediaController::class, 'index']);
        Route::post('/admin/media', [App\Http\Controllers\MediaController::class, 'store']);
        Route::delete('/admin/media/{media}', [App\Http\Controllers\MediaController::class, 'destroy']);
        
        // Media Collections
        Route::apiResource('/admin/media-collections', App\Http\Controllers\MediaCollectionController::class);
        Route::post('/admin/media-collections/{mediaCollection}/items', [App\Http\Controllers\MediaCollectionController::class, 'addItems']);
        Route::post('/admin/media-collections/{mediaCollection}/reorder', [App\Http\Controllers\MediaCollectionController::class, 'reorder']);

        // Articles
        Route::get('/admin/articles/meta', [App\Http\Controllers\ContentMetaController::class, 'index']);
        Route::get('/admin/articles', [App\Http\Controllers\ArticleController::class, 'index']);
        Route::post('/admin/articles', [App\Http\Controllers\ArticleController::class, 'store']);
        Route::get('/admin/articles/{article}', [App\Http\Controllers\ArticleController::class, 'show']);
        Route::match(['put', 'patch'], '/admin/articles/{article}', [App\Http\Controllers\ArticleController::class, 'update']);
        Route::delete('/admin/articles/{article}', [App\Http\Controllers\ArticleController::class, 'destroy']);
        
        // Workflow
        Route::post('/admin/articles/{article}/submit', [App\Http\Controllers\WorkflowController::class, 'submit']);
        Route::post('/admin/articles/{article}/approve', [App\Http\Controllers\WorkflowController::class, 'approve']);
        Route::post('/admin/articles/{article}/reject', [App\Http\Controllers\WorkflowController::class, 'reject']);
        Route::get('/admin/workflow/tasks', [App\Http\Controllers\WorkflowController::class, 'tasks']);

        // Versions
        Route::get('/admin/articles/{article}/versions', [App\Http\Controllers\VersionController::class, 'index']);
        Route::post('/admin/articles/{article}/versions', [App\Http\Controllers\VersionController::class, 'store']);
        Route::post('/admin/articles/{article}/versions/{version}/restore', [App\Http\Controllers\VersionController::class, 'restore']);

        // Scheduling
        Route::apiResource('/admin/schedules', App\Http\Controllers\ScheduleController::class)->only(['index', 'store', 'destroy']);

        // System & Analytics
        Route::get('/admin/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index']);
        Route::get('/admin/audit-logs/{auditLog}', [App\Http\Controllers\AuditLogController::class, 'show']);
        
        Route::get('/admin/settings', [App\Http\Controllers\SettingsController::class, 'index']);
        Route::post('/admin/settings', [App\Http\Controllers\SettingsController::class, 'update']);
        
        Route::apiResource('/admin/redirects', App\Http\Controllers\RedirectController::class);
        
        Route::get('/admin/analytics/dashboard', [App\Http\Controllers\AnalyticsController::class, 'dashboard']);
        Route::get('/admin/reports/daily', [App\Http\Controllers\ReportController::class, 'index']);

        // Live Updates
        Route::get('/admin/articles/{article}/live-updates', [App\Http\Controllers\LiveUpdateController::class, 'index']);
        Route::post('/admin/articles/{article}/live-updates', [App\Http\Controllers\LiveUpdateController::class, 'store']);
        Route::match(['put', 'patch'], '/admin/live-updates/{liveUpdate}', [App\Http\Controllers\LiveUpdateController::class, 'update']);
        Route::delete('/admin/live-updates/{liveUpdate}', [App\Http\Controllers\LiveUpdateController::class, 'destroy']);
        // Breaking News
        Route::get('/admin/breaking-news', [App\Http\Controllers\BreakingNewsController::class, 'index']);
        Route::post('/admin/breaking-news', [App\Http\Controllers\BreakingNewsController::class, 'store']);
        Route::delete('/admin/breaking-news/{breakingNews}', [App\Http\Controllers\BreakingNewsController::class, 'destroy']);
        // Sections
        Route::apiResource('/admin/sections', App\Http\Controllers\Admin\SectionController::class);

        // Polls (Admin)
        Route::apiResource('/admin/polls', App\Http\Controllers\Admin\PollController::class);

        // Newsletter Management
        Route::get('/admin/newsletters', [App\Http\Controllers\Admin\NewsletterController::class, 'index']);
        Route::delete('/admin/newsletters/{id}', [App\Http\Controllers\Admin\NewsletterController::class, 'destroy']);

        // Contribution Management
        Route::get('/admin/contributions', [App\Http\Controllers\Admin\ContributeController::class, 'index']);
        Route::patch('/admin/contributions/{id}/status', [App\Http\Controllers\Admin\ContributeController::class, 'updateStatus']);
        Route::delete('/admin/contributions/{id}', [App\Http\Controllers\Admin\ContributeController::class, 'destroy']);
    });
});

// Newsportal API v1
Route::prefix('v1')->middleware('throttle:1000,1')->group(function () {
    // Breaking News (Global)
    Route::get('/breaking-news', [App\Http\Controllers\BreakingNewsController::class, 'index']);
    // Homepage
    Route::get('/homepage/{edition?}', [App\Http\Controllers\Public\HomepageController::class, 'index']);

    // Articles
    Route::get('/articles/search', [App\Http\Controllers\Public\ArticleController::class, 'search']);
    Route::get('/articles/{slug}', [App\Http\Controllers\Public\ArticleController::class, 'show']);
    Route::get('/articles/{slug}/live-updates', [App\Http\Controllers\Public\ArticleController::class, 'liveUpdates']);

    // Sections & Navigation
    Route::get('/sections', [App\Http\Controllers\Public\SectionController::class, 'sections']);
    Route::get('/sections/{slug}', [App\Http\Controllers\Public\SectionController::class, 'show']);
    Route::get('/editions', [App\Http\Controllers\Public\SectionController::class, 'editions']);

    // Newsletter
    Route::post('/newsletter/subscribe', [App\Http\Controllers\Public\NewsletterController::class, 'subscribe']);
    Route::post('/newsletter/unsubscribe', [App\Http\Controllers\Public\NewsletterController::class, 'unsubscribe']);
    
    // Contribute
    Route::post('/contribute', [App\Http\Controllers\Public\ContributeController::class, 'store']);

    // Ads (Public)
    Route::get('/ads/serve', [App\Http\Controllers\Public\AdServeController::class, 'serve'])->name('api.ads.serve');
    Route::get('/ads/impression/{unit}', [App\Http\Controllers\Public\AdServeController::class, 'impression'])->name('api.ads.impression');
    Route::get('/ads/click/{unit}', [App\Http\Controllers\Public\AdServeController::class, 'click'])->name('api.ads.click');

    // Contact & Complaints
    Route::post('/contact', [App\Http\Controllers\Public\ContactController::class, 'store']);
    Route::post('/complaints', [App\Http\Controllers\Public\ComplaintController::class, 'store']);

    // Subscriptions (Public)
    Route::get('/plans', [App\Http\Controllers\Public\SubscriptionController::class, 'plans']);
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/subscribe', [App\Http\Controllers\Public\SubscriptionController::class, 'subscribe']);
        Route::get('/me/subscription', [App\Http\Controllers\Public\SubscriptionController::class, 'me']);

        // Engagement (Protected)
        Route::post('/articles/{article}/comments', [App\Http\Controllers\Public\CommentController::class, 'store']);
        Route::delete('/comments/{comment}', [App\Http\Controllers\Public\CommentController::class, 'destroy']);
        
        Route::post('/articles/{article}/react', [App\Http\Controllers\Public\ReactionController::class, 'store']);
        
        Route::post('/follow', [App\Http\Controllers\Public\FollowController::class, 'store']);
        Route::post('/unfollow', [App\Http\Controllers\Public\FollowController::class, 'destroy']);
        Route::get('/me/follows', [App\Http\Controllers\Public\FollowController::class, 'index']);

        Route::get('/me/bookmarks', [App\Http\Controllers\Public\BookmarkController::class, 'index']);
        Route::post('/articles/{article}/bookmark', [App\Http\Controllers\Public\BookmarkController::class, 'store']);
        Route::get('/articles/{article}/bookmarked', [App\Http\Controllers\Public\BookmarkController::class, 'check']);
    });

    // Engagement (Public Read)
    Route::get('/articles/{article}/comments', [App\Http\Controllers\Public\CommentController::class, 'index']);
    Route::get('/articles/{article}/reactions', [App\Http\Controllers\Public\ReactionController::class, 'stats']);
    
    // Polls
    Route::get('/polls', [App\Http\Controllers\Public\PollController::class, 'index']);
    Route::get('/polls/current', [App\Http\Controllers\Public\PollController::class, 'current']);
    Route::get('/polls/{poll}', [App\Http\Controllers\Public\PollController::class, 'show']);
    Route::post('/polls/{poll}/vote', [App\Http\Controllers\Public\PollController::class, 'vote']); 

    // Public Analytics
    Route::post('/analytics/collect', [App\Http\Controllers\AnalyticsController::class, 'store']);
    Route::get('/settings/public', [App\Http\Controllers\SettingsController::class, 'publicSettings']);

    // Admin Monetization Routes
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        Route::apiResource('advertisers', App\Http\Controllers\AdvertiserController::class);
        Route::apiResource('campaigns', App\Http\Controllers\AdCampaignController::class);
        Route::post('campaigns/{adCampaign}/units', [App\Http\Controllers\AdCampaignController::class, 'storeUnit']);
        
        Route::apiResource('ad-placements', App\Http\Controllers\AdPlacementController::class);
        Route::apiResource('ad-units', App\Http\Controllers\AdUnitController::class);
        
        Route::get('ad-stats/summary', [App\Http\Controllers\AdStatController::class, 'summary']);
        Route::get('ad-stats/placements', [App\Http\Controllers\AdStatController::class, 'placementPerformance']);
        Route::get('ad-stats', [App\Http\Controllers\AdStatController::class, 'index']);
        
        Route::apiResource('plans', App\Http\Controllers\SubscriptionPlanController::class);
        
        // User Management
        Route::apiResource('users', App\Http\Controllers\Admin\UserController::class);
    });
});
