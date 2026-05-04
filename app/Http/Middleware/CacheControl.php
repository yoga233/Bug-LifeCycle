<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Cache Control Middleware.
 * 
 * Adds cache headers to responses based on the route configuration.
 * Can be applied to routes that benefit from browser caching.
 */
class CacheControl
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $maxAge - Cache max-age in seconds
     * @param string $cacheType - 'public' or 'private'
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $maxAge = '300', string $cacheType = 'private'): Response
    {
        $response = $next($request);

        // Only add cache headers for GET requests
        if (! $request->isMethod('GET')) {
            return $response;
        }

        // Don't cache responses with user-specific data (private)
        // Only cache static/computed data that doesn't change per user
        $response->headers->set('Cache-Control', $cacheType . ', max-age=' . $maxAge . ', must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
