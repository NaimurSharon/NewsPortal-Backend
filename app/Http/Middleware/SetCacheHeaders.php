<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // Apply caching headers based on the endpoint
        if ($request->is('api/v1/homepage*')) {
            // Homepage: Cache for 5 minutes in browser/CDN
            // But immediate update due to ETag validation
            $response->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=60');
            $response->header('ETag', md5($response->getContent()));
        } elseif ($request->is('api/v1/sections*')) {
            // Section lists: Cache for 15 minutes
            $response->header('Cache-Control', 'public, max-age=900');
        } elseif ($request->is('api/v1/articles/*')) {
            // Article details: Cache for 30 minutes
            $response->header('Cache-Control', 'public, max-age=1800');
            $response->header('ETag', md5($response->getContent()));
        }

        return $response;
    }
}
