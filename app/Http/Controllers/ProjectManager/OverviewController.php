<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OverviewController extends Controller
{
    /**
     * Cache TTL for dashboard statistics (1 minute).
     */
    private const CACHE_TTL_SECONDS = 60;

    /**
     * Cache key prefix for dashboard data.
     */
    private const CACHE_PREFIX = 'pm:dashboard:';

    public function index(): View
    {
        $stats = Cache::remember(
            self::CACHE_PREFIX . 'stats',
            self::CACHE_TTL_SECONDS,
            fn () => $this->computeDashboardStats()
        );

            $newBugs = Bug::query()
                ->select([
                    'id',
                    'project_id',
                    'severity_id',
                    'priority_id',
                    'assignee_id',
                    'guest_name',
                    'title',
                    'status',
                    'created_at',
                ])
                ->with([
                    'project:id,name',
                    'priority:id,level,sla_hours,bg_color,text_color',
                    'severity:id,level,bg_color,text_color',
                    'assignee:id,name',
                ])
                ->whereNull('assignee_id')
                ->where('status', 'Reported')
                ->latest() // <-- Tetap terbaru di atas
                ->get();   // <-- Hapus limit(10) di atas baris ini

        $programmers = Cache::remember(
            self::CACHE_PREFIX . 'programmers',
            600,
            function () {
                return User::query()
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'Programmer'))
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        );

        return view('panel.project-manager.dashboard', [
            'activeCount'             => $stats['activeCount'],
            'needsAssignmentCount'    => $stats['needsAssignmentCount'],
            'oldestUnassignedMinutes' => $stats['oldestUnassignedMinutes'],
            'assignmentVariant'       => $stats['assignmentVariant'],
            'overdueSlaCount'         => $stats['overdueSlaCount'],
            'slaVariant'              => $stats['slaVariant'],
            'criticalOpenCount'       => $stats['criticalOpenCount'],
            'criticalVariant'         => $stats['criticalVariant'],
            'newBugs'                 => $newBugs,
            'programmers'             => $programmers,
        ]);
    }

    private function computeDashboardStats(): array
    {
        $openStatuses = ['Reported', 'Assigned', 'In Progress', 'Testing'];
        $now = Carbon::now();

        // ── Bug Aktif ──
        $activeCount = Bug::query()
            ->whereIn('status', $openStatuses)
            ->count();

        // ── Perlu Ditugaskan ──
        $needsAssignmentCount = Bug::query()
            ->whereNull('assignee_id')
            ->where('status', 'Reported')
            ->count();

        $oldestUnassigned = Bug::query()
            ->whereNull('assignee_id')
            ->where('status', 'Reported')
            ->orderBy('created_at', 'asc')
            ->first(['created_at']);

        $oldestUnassignedMinutes = $oldestUnassigned
            ? (int) $now->diffInMinutes($oldestUnassigned->created_at)
            : 0;

        /**
         * Hybrid logic for assignment urgency (small-scale operation):
         *
         * We evaluate urgency from 2 dimensions:
         *
         * 1. Count severity
         *    - 0 bug      -> neutral
         *    - 1-2 bug    -> action
         *    - 3-4 bug    -> warning
         *    - >= 5 bug   -> danger
         *
         * 2. Oldest unassigned age severity
         *    - no bug     -> neutral
         *    - < 2 hours  -> action
         *    - 2-8 hours  -> warning
         *    - > 8 hours  -> danger
         *
         * Final variant uses the higher severity between:
         * - count severity
         * - age severity
         *
         * Examples:
         * - 2 bugs, oldest 30 min   -> action
         * - 2 bugs, oldest 4 hours  -> warning
         * - 3 bugs, oldest 30 min   -> warning
         * - 3 bugs, oldest 10 days  -> danger
         * - 6 bugs, oldest 20 min   -> danger
         */
        $countSeverity = match (true) {
            $needsAssignmentCount === 0 => 0, // neutral
            $needsAssignmentCount <= 2  => 1, // action
            $needsAssignmentCount <= 4  => 2, // warning
            default                     => 3, // danger
        };

        $ageSeverity = match (true) {
            $needsAssignmentCount === 0        => 0, // neutral
            $oldestUnassignedMinutes <= 120    => 1, // action
            $oldestUnassignedMinutes <= 480    => 2, // warning
            default                            => 3, // danger
        };

        $finalSeverity = max($countSeverity, $ageSeverity);

        $assignmentVariant = match ($finalSeverity) {
            3       => 'danger',
            2       => 'warning',
            1       => 'action',
            default => 'neutral',
        };

        // ── SLA Terlewat ──
        $driver = DB::connection()->getDriverName();

        $overdueSlaCountQuery = Bug::query()
            ->join('priorities', 'priorities.id', '=', 'bugs.priority_id')
            ->whereIn('bugs.status', $openStatuses)
            ->whereNotNull('bugs.priority_id')
            ->where('priorities.sla_hours', '>', 0);

        if ($driver === 'sqlite') {
            $overdueSlaCountQuery->whereRaw(
                "datetime(bugs.created_at, '+' || priorities.sla_hours || ' hours') < ?",
                [$now->toDateTimeString()]
            );
        } else {
            $overdueSlaCountQuery->whereRaw(
                'DATE_ADD(bugs.created_at, INTERVAL priorities.sla_hours HOUR) < ?',
                [$now->toDateTimeString()]
            );
        }

        $overdueSlaCount = (int) $overdueSlaCountQuery->count('bugs.id');

        $slaVariant = match (true) {
            $overdueSlaCount === 0 => 'neutral',
            $overdueSlaCount <= 3  => 'warning',
            default                => 'danger',
        };

        // ── Critical Aktif ──
        $severities = app('cached_severities');
        $criticalSeverityId = $severities->firstWhere('level', 'Critical')?->id;

        $criticalOpenCount = $criticalSeverityId
            ? Bug::query()
                ->where('severity_id', $criticalSeverityId)
                ->whereIn('status', $openStatuses)
                ->count()
            : 0;

        $criticalVariant = $criticalOpenCount > 0 ? 'danger' : 'neutral';

        return [
            'activeCount'             => $activeCount,
            'needsAssignmentCount'    => $needsAssignmentCount,
            'oldestUnassignedMinutes' => $oldestUnassignedMinutes,
            'assignmentVariant'       => $assignmentVariant,
            'overdueSlaCount'         => $overdueSlaCount,
            'slaVariant'              => $slaVariant,
            'criticalOpenCount'       => $criticalOpenCount,
            'criticalVariant'         => $criticalVariant,
        ];
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'stats');
    }
}