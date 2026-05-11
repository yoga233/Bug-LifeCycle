<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\BugStatusHistory;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    /**
     * Cache TTL for programmers list (10 minutes).
     */
    private const CACHE_PROGRAMMERS_TTL = 600;

    /**
     * Cache prefix for PM performance data.
     */
    private const CACHE_PREFIX = 'pm:performance:';

    public function index(Request $request, TicketService $tickets): View
    {
        $timezone = (string) config('app.timezone', 'Asia/Jakarta');
        $now = now($timezone);
        [$fromAt, $toAt, $dateFrom, $dateTo, $allTime] = $this->resolveDateRange($request, $now, $timezone);

        // Filter by programmer/assignee_id (optional)
        $assigneeId = $request->query('assignee_id');

        // Cache active programmers list for 10 minutes (they change rarely)
        $programmers = Cache::remember(
            self::CACHE_PREFIX . 'programmers',
            self::CACHE_PROGRAMMERS_TTL,
            function () {
                return User::query()
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'Programmer'))
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        );

        $programmerIds = $programmers->pluck('id')->all();
        if ($assigneeId && ! in_array((int) $assigneeId, $programmerIds, true)) {
            // Invalid/foreign id: ignore to avoid leaking other user info
            $assigneeId = null;
        }

        $selectedAssigneeId = $assigneeId ? (int) $assigneeId : null;

        $resolvedBaseQuery = $this->resolvedBaseQuery($fromAt, $toAt, $selectedAssigneeId, $programmerIds);

        $perPage = 8;

        $resolved = (clone $resolvedBaseQuery)
            ->select('bug_status_histories.*')
            ->with([
                'bug.project',
                'bug.assignee:id,name',
                'bug.priority:id,level,sla_hours',
            ])
            ->orderByDesc('bug_status_histories.changed_at')
            ->paginate($perPage)
            ->withQueryString();

        $slaSummary = null;
        $slaByHistoryId = collect();

        if ($selectedAssigneeId) {
            [
                'summary' => $slaSummary,
                'by_history_id' => $slaByHistoryId,
            ] = $this->buildSlaCompliance(
                fromAt: $fromAt,
                toAt: $toAt,
                assigneeId: $selectedAssigneeId,
                programmerIds: $programmerIds,
                timezone: $timezone,
                tickets: $tickets,
            );
        } else {
            // Semua programmer: hitung SLA per history_id tanpa filter assignee
            [
                'by_history_id' => $slaByHistoryId,
            ] = $this->buildSlaCompliance(
                fromAt: $fromAt,
                toAt: $toAt,
                assigneeId: null,             // ← null = semua programmer
                programmerIds: $programmerIds,
                timezone: $timezone,
                tickets: $tickets,
            );
        }

        // Attach derived ticket display for UI consistency.
        $resolved->getCollection()
            ->transform(function (BugStatusHistory $h) use ($tickets, $slaByHistoryId) {
                if ($h->bug) {
                    $h->bug->setAttribute('ticket', $tickets->fromBugId($h->bug->id));
                }

                // Selalu attach SLA jika tersedia (baik per programmer maupun semua)
                $h->setAttribute('sla', $slaByHistoryId->get((int) $h->id));

                return $h;
            });

        // Reuse paginator total to avoid duplicate aggregate count query.
        $fixedInRange = (int) $resolved->total();

        $totalFixed = (int) $this->resolvedBaseQuery(
            fromAt: null,
            toAt: null,
            assigneeId: $selectedAssigneeId,
            programmerIds: $programmerIds,
        )->count('bug_status_histories.id');

        $thisMonth = (int) $this->resolvedBaseQuery(
            fromAt: $now->copy()->startOfMonth(),
            toAt: $now->copy()->endOfMonth(),
            assigneeId: $selectedAssigneeId,
            programmerIds: $programmerIds,
        )->count('bug_status_histories.id');

        $selectedProgrammer = null;
        if ($selectedAssigneeId) {
            $selectedProgrammer = $programmers->firstWhere('id', $selectedAssigneeId);
        }

        $filters = [
            'assignee_id' => $selectedAssigneeId ? (string) $selectedAssigneeId : '',
            'from' => $dateFrom,
            'to' => $dateTo,
            'all_time' => $allTime ? '1' : '0',
        ];

        // Analytics: jumlah bug selesai per programmer pada periode terpilih.
        // Catatan: gunakan query agregasi agar lebih ringan daripada groupBy koleksi.
        $resolvedCurrentPeriod = $this->aggregateResolvedPerProgrammer(
            fromAt: $fromAt,
            toAt: $toAt,
            programmerIds: $programmerIds,
        );

        $chart = $programmers->map(function (User $p) use ($resolvedCurrentPeriod) {
            $row = $resolvedCurrentPeriod[$p->id] ?? null;

            $current = (int) ($row->total ?? 0);
            $slaMet = (int) ($row->sla_met ?? 0);
            $slaBreached = (int) ($row->sla_breached ?? 0);

            $withoutTarget = (int) ($row->without_target ?? max(0, $current - ($slaMet + $slaBreached)));

            $worstDelayMinutes = (int) ($row->worst_delay_minutes ?? 0);
            $tightMarginMinutes = $row && isset($row->tight_margin_minutes)
                ? (int) round((float) $row->tight_margin_minutes)
                : 0;

            return [
                'id' => $p->id,
                'label' => $p->name,
                'current' => $current,
                'sla_met' => max(0, $slaMet),
                'sla_breached' => max(0, $slaBreached),
                'without_target' => max(0, $withoutTarget),
                'worst_delay_minutes' => max(0, $worstDelayMinutes),
                'tight_margin_minutes' => max(0, $tightMarginMinutes),
            ];
        })->values();

        $chartTotals = [
            'current' => (int) $chart->sum('current'),
            'sla_met' => (int) $chart->sum('sla_met'),
            'sla_breached' => (int) $chart->sum('sla_breached'),
            'without_target' => (int) $chart->sum('without_target'),
        ];

        // Untuk tampilan human-friendly (Indonesia)
        $dateFromLabel = Carbon::createFromFormat('Y-m-d', $dateFrom, $timezone)->locale('id')->translatedFormat('d M Y');
        $dateToLabel = Carbon::createFromFormat('Y-m-d', $dateTo, $timezone)->locale('id')->translatedFormat('d M Y');
        $periodLabel = $allTime
            ? 'Semua waktu'
            : $dateFromLabel.' – '.$dateToLabel;

        return view('panel.project-manager.kinerja', compact(
            'programmers',
            'selectedProgrammer',
            'dateFrom',
            'dateTo',
            'dateFromLabel',
            'dateToLabel',
            'periodLabel',
            'allTime',
            'filters',
            'resolved',
            'fixedInRange',
            'totalFixed',
            'thisMonth',
            'chart',
            'chartTotals',
            'slaSummary',
            'timezone',
        ));
    }

    private function buildSlaCompliance(
        ?Carbon $fromAt,
        ?Carbon $toAt,
        ?int $assigneeId,      // ← nullable
        array $programmerIds,
        string $timezone,
        TicketService $tickets,
    ): array
    {
        $rows = $this->resolvedBaseQuery(
            fromAt: $fromAt,
            toAt: $toAt,
            assigneeId: $assigneeId,
            programmerIds: $programmerIds,
        )
            ->leftJoin('priorities', 'priorities.id', '=', 'bugs.priority_id')
            ->select([
                'bug_status_histories.id as history_id',
                'bug_status_histories.changed_at as resolved_at',
                'bugs.id as bug_id',
                'bugs.title as bug_title',
                'bugs.created_at as bug_created_at',
                'priorities.sla_hours as target_sla_hours',
            ])
            ->orderByDesc('bug_status_histories.changed_at')
            ->get();

        $items = $rows
            ->map(function (BugStatusHistory $row) use ($timezone, $tickets): array {
                $resolvedAt = $this->parseDateTimeValue($row->resolved_at ?? null, $timezone);
                $createdAt = $this->parseDateTimeValue($row->bug_created_at ?? null, $timezone);
                $bugId = (int) ($row->bug_id ?? 0);

                $targetSlaHours = is_numeric($row->target_sla_hours)
                    ? (int) $row->target_sla_hours
                    : null;

                $actualMinutes = null;
                if ($resolvedAt && $createdAt) {
                    $actualMinutes = max(0, $createdAt->diffInMinutes($resolvedAt, false));
                }

                $targetSlaMinutes = $targetSlaHours !== null
                    ? ($targetSlaHours * 60)
                    : null;

                $isEvaluable = $targetSlaHours !== null
                    && $targetSlaHours > 0
                    && $actualMinutes !== null;

                $deltaMinutes = ($isEvaluable && $targetSlaMinutes !== null && $actualMinutes !== null)
                    ? ($actualMinutes - $targetSlaMinutes)
                    : null;

                $isOnTime = $isEvaluable
                    ? $actualMinutes <= ($targetSlaMinutes ?? 0)
                    : null;

                $status = $isEvaluable
                    ? ($isOnTime ? 'on_time' : 'late')
                    : 'unknown';

                $detailNote = null;
                if ($status === 'late' && $deltaMinutes !== null && $deltaMinutes > 0) {
                    $detailNote = 'Terlambat '.$this->formatDurationMinutes($deltaMinutes);
                } elseif ($status === 'on_time' && $deltaMinutes !== null && $deltaMinutes < 0) {
                    $detailNote = 'Lebih cepat '.$this->formatDurationMinutes(abs($deltaMinutes));
                } elseif ($status === 'on_time') {
                    $detailNote = 'Tepat waktu';
                } elseif ($status === 'unknown') {
                    $detailNote = 'SLA belum dapat dihitung';
                }

                $ticketLabel = $bugId > 0 ? ('#BUG-' . sprintf('%06d', $bugId)) : '-';
                if ($bugId > 0) {
                    try {
                        $ticketLabel = '#' . $tickets->fromBugId($bugId);
                    } catch (\Throwable) {
                        $ticketLabel = '#BUG-' . sprintf('%06d', $bugId);
                    }
                }

                return [
                    'history_id' => (int) $row->history_id,
                    'bug_id' => $bugId,
                    'ticket' => $ticketLabel,
                    'bug_title' => (string) ($row->bug_title ?? ''),
                    'target_sla_hours' => $targetSlaHours,
                    'target_sla_minutes' => $targetSlaMinutes,
                    'actual_completion_minutes' => $actualMinutes,
                    'comparison_label' => ($targetSlaMinutes !== null && $actualMinutes !== null)
                        ? $this->formatDurationMinutes($targetSlaMinutes).' | '.$this->formatDurationMinutes($actualMinutes)
                        : null,
                    'comparison_ratio_label' => ($targetSlaMinutes !== null && $actualMinutes !== null)
                        ? $this->formatDurationMinutes($targetSlaMinutes).'/'.$this->formatDurationMinutes($actualMinutes)
                        : null,
                    'detail_note' => $detailNote,
                    'delta_minutes' => $deltaMinutes,
                    'status' => $status,
                    'is_evaluable' => $isEvaluable,
                    'is_on_time' => $isOnTime,
                    'resolved_at_timestamp' => $resolvedAt?->timestamp,
                    'resolved_at_label' => $resolvedAt
                        ? $resolvedAt->copy()->locale('id')->translatedFormat('d M Y, H:i')
                        : null,
                ];
            })
            ->values();

        $evaluated = $items->where('is_evaluable', true);
        $onTimeCount = (int) $evaluated->where('is_on_time', true)->count();
        $lateCount = (int) $evaluated->where('is_on_time', false)->count();
        $evaluatedCount = $onTimeCount + $lateCount;
        $withoutTargetCount = (int) $items->where('is_evaluable', false)->count();

        $compliancePercent = $evaluatedCount > 0
            ? round(($onTimeCount / $evaluatedCount) * 100, 1)
            : 0.0;

        $breachPercent = $evaluatedCount > 0
            ? round(($lateCount / $evaluatedCount) * 100, 1)
            : 0.0;

        $avgTargetMinutes = $evaluatedCount > 0
            ? (int) round((float) $evaluated->avg(fn (array $item) => (int) ($item['target_sla_minutes'] ?? 0)))
            : 0;

        $avgActualMinutes = $evaluatedCount > 0
            ? (int) round((float) $evaluated->avg(fn (array $item) => (int) ($item['actual_completion_minutes'] ?? 0)))
            : 0;

        $lateDelayMinutes = $evaluated
            ->where('is_on_time', false)
            ->pluck('delta_minutes')
            ->filter(fn ($value) => is_numeric($value) && (float) $value > 0)
            ->map(fn ($value) => (int) round((float) $value))
            ->values();

        $worstDelayMinutes = $lateDelayMinutes->isNotEmpty()
            ? (int) $lateDelayMinutes->max()
            : 0;

        $avgBreachDelayMinutes = $lateDelayMinutes->isNotEmpty()
            ? (int) round((float) $lateDelayMinutes->avg())
            : 0;

        $nearBreachCount = (int) $evaluated
            ->filter(function (array $item): bool {
                $deltaMinutes = $item['delta_minutes'] ?? null;
                if (! is_numeric($deltaMinutes)) {
                    return false;
                }

                $deltaMinutes = (int) round((float) $deltaMinutes);

                // Near breach: selesai tepat waktu, namun margin <= 2 jam sebelum SLA limit.
                return $deltaMinutes <= 0 && $deltaMinutes >= -120;
            })
            ->count();

        $riskStatus = 'unknown';
        $riskLabel = 'Belum ada data';

        if ($evaluatedCount > 0) {
            if ($compliancePercent >= 90) {
                $riskStatus = 'safe';
                $riskLabel = 'Aman';
            } elseif ($compliancePercent >= 80) {
                $riskStatus = 'warning';
                $riskLabel = 'Waspada';
            } else {
                $riskStatus = 'critical';
                $riskLabel = 'Kritis';
            }
        }

        $insightHeadline = match (true) {
            $evaluatedCount <= 0 => 'Belum ada tiket terukur SLA pada periode ini.',
            $lateCount <= 0 => 'Semua tiket terukur selesai tepat SLA.',
            $lateCount === 1 => 'Ada 1 tiket lewat SLA, perlu perhatian cepat.',
            default => "Ada {$lateCount} tiket lewat SLA, perlu prioritas tindak lanjut.",
        };

        $totalTargetMinutes = (int) $evaluated->sum(fn (array $item) => (int) ($item['target_sla_minutes'] ?? 0));
        $totalActualMinutes = (int) $evaluated->sum(fn (array $item) => (int) ($item['actual_completion_minutes'] ?? 0));
        $remainingMinutes = $totalTargetMinutes - $totalActualMinutes;

        $chartItems = $items
            ->where('is_evaluable', true)
            ->values()
            ->map(static function (array $item): array {
                return [
                    'history_id' => $item['history_id'],
                    'ticket' => $item['ticket'],
                    'title' => $item['bug_title'],
                    'target_sla_minutes' => $item['target_sla_minutes'],
                    'actual_completion_minutes' => $item['actual_completion_minutes'],
                    'comparison_ratio_label' => $item['comparison_ratio_label'],
                    'status' => $item['status'],
                    'detail_note' => $item['detail_note'],
                    'resolved_at_timestamp' => $item['resolved_at_timestamp'],
                    'resolved_at_label' => $item['resolved_at_label'],
                ];
            })
            ->all();

        $timeSortedItems = $items
            ->where('is_evaluable', true)
            ->sort(static function (array $a, array $b): int {
                $resolvedAtA = (int) ($a['resolved_at_timestamp'] ?? 0);
                $resolvedAtB = (int) ($b['resolved_at_timestamp'] ?? 0);

                if ($resolvedAtA !== $resolvedAtB) {
                    return $resolvedAtB <=> $resolvedAtA;
                }

                return ((int) ($b['history_id'] ?? 0)) <=> ((int) ($a['history_id'] ?? 0));
            })
            ->values()
            ->map(static function (array $item): array {
                $deltaMinutes = (int) ($item['delta_minutes'] ?? 0);

                return [
                    'history_id' => $item['history_id'],
                    'ticket' => $item['ticket'],
                    'title' => $item['bug_title'],
                    'target_sla_minutes' => (int) ($item['target_sla_minutes'] ?? 0),
                    'actual_completion_minutes' => (int) ($item['actual_completion_minutes'] ?? 0),
                    'delta_minutes' => $deltaMinutes,
                    'variance_minutes' => abs($deltaMinutes),
                    'status' => $item['status'],
                    'detail_note' => $item['detail_note'],
                    'resolved_at_label' => $item['resolved_at_label'],
                ];
            })
            ->all();

        return [
            'summary' => [
                'on_time_count' => $onTimeCount,
                'late_count' => $lateCount,
                'evaluated_count' => $evaluatedCount,
                'without_target_count' => $withoutTargetCount,
                'compliance_percent' => $compliancePercent,
                'breach_percent' => $breachPercent,
                'avg_target_minutes' => $avgTargetMinutes,
                'avg_actual_minutes' => $avgActualMinutes,
                'worst_delay_minutes' => $worstDelayMinutes,
                'avg_breach_delay_minutes' => $avgBreachDelayMinutes,
                'near_breach_count' => $nearBreachCount,
                'risk_status' => $riskStatus,
                'risk_label' => $riskLabel,
                'insight_headline' => $insightHeadline,
                'total_target_minutes' => $totalTargetMinutes,
                'total_actual_minutes' => $totalActualMinutes,
                'remaining_minutes' => $remainingMinutes,
                'chart_items' => $chartItems,
                'ranked_items' => $timeSortedItems,
            ],
            'by_history_id' => $items->keyBy('history_id'),
        ];
    }

    private function parseDateTimeValue(mixed $value, string $timezone): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->timezone($timezone);
        }

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse((string) $value, $timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    private function aggregateResolvedPerProgrammer(?Carbon $fromAt, ?Carbon $toAt, array $programmerIds): Collection
    {
        $actualMinutesExpr = 'GREATEST(TIMESTAMPDIFF(MINUTE, bugs.created_at, bug_status_histories.changed_at), 0)';
        $targetMinutesExpr = '(priorities.sla_hours * 60)';
        $isEvaluableExpr = 'priorities.sla_hours IS NOT NULL AND priorities.sla_hours > 0 AND bugs.created_at IS NOT NULL AND bug_status_histories.changed_at IS NOT NULL';
        $isMetExpr = "({$isEvaluableExpr} AND {$actualMinutesExpr} <= {$targetMinutesExpr})";
        $isBreachedExpr = "({$isEvaluableExpr} AND {$actualMinutesExpr} > {$targetMinutesExpr})";

        $query = BugStatusHistory::query()
            ->join('bugs', 'bug_status_histories.bug_id', '=', 'bugs.id')
            ->leftJoin('priorities', 'priorities.id', '=', 'bugs.priority_id')
            ->selectRaw('bugs.assignee_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN {$isMetExpr} THEN 1 ELSE 0 END) as sla_met")
            ->selectRaw("SUM(CASE WHEN {$isBreachedExpr} THEN 1 ELSE 0 END) as sla_breached")
            ->selectRaw("SUM(CASE WHEN {$isEvaluableExpr} THEN 0 ELSE 1 END) as without_target")
            ->selectRaw("MAX(CASE WHEN {$isBreachedExpr} THEN ({$actualMinutesExpr} - {$targetMinutesExpr}) ELSE 0 END) as worst_delay_minutes")
            ->selectRaw("MIN(CASE WHEN {$isMetExpr} THEN ({$targetMinutesExpr} - {$actualMinutesExpr}) ELSE NULL END) as tight_margin_minutes")
            ->where('bug_status_histories.new_status', 'Resolved')
            ->whereNull('bugs.deleted_at')
            ->whereIn('bugs.assignee_id', $programmerIds)
            ->groupBy('bugs.assignee_id');

        if ($fromAt && $toAt) {
            $query->whereBetween('bug_status_histories.changed_at', [$fromAt, $toAt]);
        }

        return $query->get()->keyBy('assignee_id');
    }

    private function resolvedBaseQuery(?Carbon $fromAt, ?Carbon $toAt, ?int $assigneeId, array $programmerIds): Builder
    {
        $query = BugStatusHistory::query()
            ->join('bugs', 'bugs.id', '=', 'bug_status_histories.bug_id')
            ->where('bug_status_histories.new_status', 'Resolved')
            ->whereNull('bugs.deleted_at');

        if ($fromAt && $toAt) {
            $query->whereBetween('bug_status_histories.changed_at', [$fromAt, $toAt]);
        }

        if ($assigneeId) {
            $query->where('bugs.assignee_id', $assigneeId);
        } else {
            // For PM view, only count programmer assignees (exclude null / non-programmer)
            $query->whereIn('bugs.assignee_id', $programmerIds);
        }

        return $query;
    }

    private function resolveDateRange(Request $request, Carbon $now, string $timezone): array
    {
        $allTime = $request->boolean('all_time');

        $dateFrom = $this->parseDate(
            $request->string('from')->toString(),
            $now->copy()->startOfMonth(),
            $timezone,
        )->startOfDay();

        $dateTo = $this->parseDate(
            $request->string('to')->toString(),
            $now->copy(),
            $timezone,
        )->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [
                $dateTo->copy()->startOfDay(),
                $dateFrom->copy()->endOfDay(),
            ];
        }

        return [
            $allTime ? null : $dateFrom,
            $allTime ? null : $dateTo,
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
            $allTime,
        ];
    }

    private function parseDate(string $value, Carbon $fallback, string $timezone): Carbon
    {
        if ($value === '') {
            return $fallback->copy();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value, $timezone);
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    private function formatDurationMinutes(int $minutes): string
    {
        $minutes = max(0, $minutes);

        $days = intdiv($minutes, 1440);
        $remaining = $minutes % 1440;
        $hours = intdiv($remaining, 60);
        $mins = $remaining % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.' hari';
        }
        if ($hours > 0) {
            $parts[] = $hours.' jam';
        }
        if ($mins > 0 || empty($parts)) {
            $parts[] = $mins.' menit';
        }

        return implode(' ', $parts);
    }
}
