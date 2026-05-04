<?php

namespace App\Services;

use App\Models\Bug;
use Illuminate\Support\Facades\Cache;

/**
 * Service for handling cache invalidation across the application.
 * 
 * This ensures cache consistency when data changes.
 */
class CacheInvalidationService
{
    /**
     * PM Dashboard cache prefix
     */
    private const PM_DASHBOARD_PREFIX = 'pm:dashboard:';
    private const PM_PERFORMANCE_PREFIX = 'pm:performance:';

    /**
     * Invalidate PM dashboard cache.
     * 
     * @param int|null $userId Specific PM user ID, or null for all PMs
     * @return void
     */
    public function invalidatePmDashboard(?int $userId = null): void
    {
        // For time-based cache keys, we need to clear recent hours
        $now = now();
        
        // Clear current hour and last few hours
        for ($i = 0; $i <= 2; $i++) {
            $hourKey = $now->copy()->subHours($i)->format('Y-m-d-H');
            $key = $this->buildDashboardStatsKey($hourKey, $userId);
            Cache::forget($key);
        }

        // Clear programmers list cache
        $programmersKey = $this->buildProgrammersKey($userId);
        Cache::forget($programmersKey);

        // Clear performance page cache
        $perfKey = $this->buildPerformanceProgrammersKey($userId);
        Cache::forget($perfKey);
    }

    /**
     * Invalidate cache when bug data changes.
     * 
     * @param Bug $bug The bug that was changed
     * @param string $changeType Type of change: 'created', 'updated', 'deleted', 'status_changed', 'assigned'
     * @return void
     */
    public function invalidateOnBugChange(Bug $bug, string $changeType = 'updated'): void
    {
        // Always invalidate dashboard stats - bug data affects counts
        $this->invalidatePmDashboard();

        // Invalidate performance stats if status changed
        if (in_array($changeType, ['status_changed', 'assigned', 'created', 'deleted'])) {
            $this->invalidatePmPerformance();
        }

        // If bug was assigned/unassigned, invalidate for specific assignee
        if ($changeType === 'assigned' && $bug->assignee_id) {
            // The assignee will see different stats, so invalidate their cache
            // For now, we invalidate all as simplest approach
            $this->invalidatePmDashboard();
        }
    }

    /**
     * Invalidate PM performance page cache.
     * 
     * @param int|null $userId Specific PM user ID, or null for all
     * @return void
     */
    public function invalidatePmPerformance(?int $userId = null): void
    {
        $programmersKey = $this->buildPerformanceProgrammersKey($userId);
        Cache::forget($programmersKey);
    }

    /**
     * Invalidate master data cache (severities, priorities).
     * 
     * @return void
     */
    public function invalidateMasterData(): void
    {
        Cache::forget('severities:all');
        Cache::forget('priorities:all');
    }

    /**
     * Invalidate all application cache (use with caution).
     * 
     * @return void
     */
    public function invalidateAll(): void
    {
        $this->invalidatePmDashboard();
        $this->invalidateMasterData();
        Cache::flush();
    }

    /**
     * Build cache key for dashboard stats.
     * 
     * @param string $hourKey Hour identifier (Y-m-d-H format)
     * @param int|null $userId User ID for user-specific cache
     * @return string
     */
    private function buildDashboardStatsKey(string $hourKey, ?int $userId = null): string
    {
        if ($userId) {
            return self::PM_DASHBOARD_PREFIX . "stats:user{$userId}:{$hourKey}";
        }
        return self::PM_DASHBOARD_PREFIX . "stats:{$hourKey}";
    }

    /**
     * Build cache key for programmers list.
     * 
     * @param int|null $userId User ID for user-specific cache
     * @return string
     */
    private function buildProgrammersKey(?int $userId = null): string
    {
        if ($userId) {
            return self::PM_DASHBOARD_PREFIX . "programmers:user{$userId}";
        }
        return self::PM_DASHBOARD_PREFIX . 'programmers';
    }

    /**
     * Build cache key for performance page programmers list.
     * 
     * @param int|null $userId User ID for user-specific cache
     * @return string
     */
    private function buildPerformanceProgrammersKey(?int $userId = null): string
    {
        if ($userId) {
            return self::PM_PERFORMANCE_PREFIX . "programmers:user{$userId}";
        }
        return self::PM_PERFORMANCE_PREFIX . 'programmers';
    }
}
