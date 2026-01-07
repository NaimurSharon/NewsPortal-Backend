<?php

namespace App\Services;

use App\Models\Edition;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Http;

class CacheService
{
    /**
     * Clear all homepage and section caches.
     * Often called after article publishing or section updates.
     */
    public static function clearPublicCaches()
    {
        // Clear all edition-specific homepages
        $editions = Edition::all();
        foreach ($editions as $edition) {
            Cache::forget("homepage_{$edition->code}");
        }

        // Trigger Next.js Revalidation
        // This would be your Next.js API route that handles on-demand revalidation
        $nextUrl = env('NEXTJS_REVALIDATE_URL');
        $secret = env('NEXTJS_REVALIDATE_SECRET');

        if ($nextUrl && $secret) {
            Http::post($nextUrl, [
                'secret' => $secret,
                'paths' => ['/', '/news', '/sport'] // Dynamic paths can be added here
            ]);
        }
    }
}
