<?php

namespace App\Services;

use App\Models\GuestBugReport;
use App\Models\GuestRateLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestSpamProtectionService
{
    /**
     * Rate limit configurations
     */
    private const MAX_REPORTS_PER_HOUR = 5;
    private const MAX_REPORTS_PER_DAY = 20;
    private const BLOCK_DURATION_MINUTES = 60;
    private const MAX_REPORTS_PER_IP_PER_DAY = 50; // Hard limit for IP

    /**
     * Check if the guest can submit a bug report
     * Returns array with 'allowed' boolean and 'reason' string if not allowed
     */
    public function checkRateLimit(Request $request, string $email): array
    {
        $ipAddress = $request->ip();
        
        // First, check IP-based hard limit
        $ipLimit = $this->checkIpHardLimit($ipAddress);
        if (!$ipLimit['allowed']) {
            return $ipLimit;
        }

        // Check email-based rate limit
        $emailLimit = $this->checkEmailRateLimit($ipAddress, $email);
        if (!$emailLimit['allowed']) {
            return $emailLimit;
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Hard limit based on IP address (prevent massive bot attacks)
     */
    private function checkIpHardLimit(string $ipAddress): array
    {
        $today = now()->startOfDay();
        
        $ipReportCount = GuestBugReport::where('ip_address', $ipAddress)
            ->where('reported_at', '>=', $today)
            ->count();

        if ($ipReportCount >= self::MAX_REPORTS_PER_IP_PER_DAY) {
            Log::warning('Guest spam protection: IP hard limit reached', [
                'ip' => $ipAddress,
                'count' => $ipReportCount,
            ]);

            return [
                'allowed' => false,
                'reason' => __('client-report.spam.ip_hard_limit'),
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Email-based rate limiting with progressive blocking
     */
    private function checkEmailRateLimit(string $ipAddress, string $email): array
    {
        $rateLimit = GuestRateLimit::firstOrCreate(
            ['ip_address' => $ipAddress, 'email' => $email],
            [
                'report_count' => 0,
                'first_report_at' => now(),
                'last_report_at' => now(),
                'is_blocked' => false,
            ]
        );

        // Check if currently blocked
        if ($rateLimit->isBlocked()) {
            $remainingTime = now()->diffInMinutes($rateLimit->blocked_until);
            
            Log::warning('Guest spam protection: Blocked guest tried to report', [
                'ip' => $ipAddress,
                'email' => $email,
                'blocked_until' => $rateLimit->blocked_until,
            ]);

            return [
                'allowed' => false,
                'reason' => __('client-report.spam.temporarily_blocked', [
                    'minutes' => $remainingTime,
                ]),
            ];
        }

        // Check rate limits
        if (!$rateLimit->canSubmitReport(self::MAX_REPORTS_PER_HOUR, self::MAX_REPORTS_PER_DAY)) {
            // Auto-block if exceeded
            $rateLimit->block('Melebihi batas rate limit', self::BLOCK_DURATION_MINUTES);
            
            Log::warning('Guest spam protection: Auto-blocking due to rate limit exceeded', [
                'ip' => $ipAddress,
                'email' => $email,
                'report_count' => $rateLimit->report_count,
            ]);

            return [
                'allowed' => false,
                'reason' => __('client-report.spam.rate_limit_exceeded', [
                    'minutes' => self::BLOCK_DURATION_MINUTES,
                ]),
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Record a new report submission
     */
    public function recordReport(Request $request, string $email): void
    {
        $ipAddress = $request->ip();

        // Update or create rate limit record
        $rateLimit = GuestRateLimit::firstOrCreate(
            ['ip_address' => $ipAddress, 'email' => $email],
            [
                'first_report_at' => now(),
                'last_report_at' => now(),
            ]
        );

        // If exists, increment count
        if ($rateLimit->wasRecentlyCreated === false) {
            $rateLimit->incrementReportCount();
        }

        Log::info('Guest bug report recorded', [
            'ip' => $ipAddress,
            'email' => $email,
            'report_count' => $rateLimit->report_count,
        ]);
    }

    /**
     * Get pending reports count for dashboard
     */
    public function getPendingCount(): int
    {
        return GuestBugReport::where('queue_status', 'pending')->count();
    }

    /**
     * Get today's report count for dashboard
     */
    public function getTodayCount(): int
    {
        return GuestBugReport::whereDate('reported_at', today())->count();
    }

    /**
     * Get blocked IPs count
     */
    public function getBlockedCount(): int
    {
        return GuestRateLimit::where('is_blocked', true)
            ->where(function ($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->count();
    }
}
