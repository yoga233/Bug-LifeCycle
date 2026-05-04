<?php

namespace App\Http\Middleware;

use App\Services\GuestSpamProtectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to bug report routes
        if (!$this->isBugReportRoute($request)) {
            return $next($request);
        }

        // Skip if user is authenticated
        if (auth()->check()) {
            return $next($request);
        }

        // Get email from request if available
        $email = $request->input('guest_email', '');
        
        if (empty($email)) {
            // Allow to pass - validation will catch missing email
            return $next($request);
        }

        // Check rate limit
        $spamProtection = app(GuestSpamProtectionService::class);
        $checkResult = $spamProtection->checkRateLimit($request, $email);

        if (!$checkResult['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $checkResult['reason'],
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }

        return $next($request);
    }

    /**
     * Check if the request is for bug reporting
     */
    private function isBugReportRoute(Request $request): bool
    {
        return $request->is('report') || 
               $request->is('api/report') ||
               $request->routeIs('client.report.store');
    }
}
