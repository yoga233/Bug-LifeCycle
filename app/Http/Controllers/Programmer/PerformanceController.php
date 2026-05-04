<?php

namespace App\Http\Controllers\Programmer;

use App\Http\Controllers\Controller;
use App\Models\BugStatusHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $timezone = (string) config('app.timezone', 'Asia/Jakarta');
        $now = now($timezone);

        [$fromAt, $toAt, $dateFrom, $dateTo] = $this->resolveDateRange($request, $now, $timezone);

        $resolvedBaseQuery = $this->resolvedBaseQuery($user->id, $fromAt, $toAt);

        $resolved = (clone $resolvedBaseQuery)
            ->select('bug_status_histories.*')
            ->with([
                'bug:id,project_id,priority_id,title,created_at',
                'bug.project:id,name',
                'bug.priority:id,level,sla_hours',
            ])
            ->orderByDesc('bug_status_histories.changed_at')
            ->paginate(15)
            ->withQueryString();

        // Optimized: Get totalFixed and thisMonth in single query using conditional aggregation
        $statsQuery = clone $resolvedBaseQuery;
        $stats = $statsQuery
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN bug_status_histories.changed_at >= ? THEN 1 ELSE 0 END) as this_month", 
                [$now->copy()->startOfMonth()])
            ->first();
        
        $totalFixed = (int) ($stats->total ?? 0);
        $thisMonth = (int) ($stats->this_month ?? 0);

        // Get top project - use cloned query
        $topProjectQuery = clone $resolvedBaseQuery;
        $topProjectRow = $topProjectQuery
            ->join('projects', 'projects.id', '=', 'bugs.project_id')
            ->selectRaw('projects.name as project_name, COUNT(*) as total')
            ->groupBy('projects.id', 'projects.name')
            ->orderByDesc('total')
            ->first();

        $topProject = $topProjectRow?->project_name;
        $topProjectCount = (int) ($topProjectRow->total ?? 0);

        $monthlyData = $this->buildMonthlyData($user->id, $timezone, $fromAt, $toAt);

        $slaTimeline = $this->buildSlaTimeline($resolved->getCollection(), $timezone);

        $dateFromLabel = Carbon::createFromFormat('Y-m-d', $dateFrom, $timezone)
            ->locale('id')
            ->translatedFormat('d M Y');

        $dateToLabel = Carbon::createFromFormat('Y-m-d', $dateTo, $timezone)
            ->locale('id')
            ->translatedFormat('d M Y');

        return view('panel.programmer.kinerja', compact(
            'dateFrom',
            'dateTo',
            'dateFromLabel',
            'dateToLabel',
            'resolved',
            'totalFixed',
            'thisMonth',
            'topProject',
            'topProjectCount',
            'monthlyData',
            'slaTimeline',
            'timezone',
        ));
    }

    private function buildMonthlyData(int $assigneeId, string $timezone, Carbon $fromAt, Carbon $toAt): Collection
    {
        // Bangun daftar bulan dari rentang filter
        $start  = $fromAt->copy()->startOfMonth();
        $end    = $toAt->copy()->startOfMonth();
        $months = collect();

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $months->push($cursor->copy());
            $cursor->addMonth();
        }

        // Batasi maksimal 12 bulan agar query tidak terlalu berat
        if ($months->count() > 12) {
            $months = $months->slice(-12)->values();
        }

        if ($months->isEmpty()) {
            return collect();
        }

        // Build conditional aggregation per bulan
        $selectParts = [];
        $bindings    = [];

        foreach ($months as $i => $m) {
            $selectParts[] = "SUM(CASE WHEN bug_status_histories.changed_at >= ? AND bug_status_histories.changed_at < ? THEN 1 ELSE 0 END) as m{$i}";
            $bindings[]    = $m->copy()->startOfMonth()->toDateTimeString();
            $bindings[]    = $m->copy()->endOfMonth()->toDateTimeString();
        }

        $monthlyCounts = BugStatusHistory::query()
            ->join('bugs', 'bugs.id', '=', 'bug_status_histories.bug_id')
            ->where('bug_status_histories.new_status', 'Resolved')
            ->where('bugs.assignee_id', $assigneeId)
            ->whereNull('bugs.deleted_at')
            ->whereBetween('bug_status_histories.changed_at', [
                $months->first()->copy()->startOfMonth()->toDateTimeString(),
                $months->last()->copy()->endOfMonth()->toDateTimeString(),
            ])
            ->selectRaw(implode(', ', $selectParts), $bindings)
            ->first();

        return $months->map(function (Carbon $m, int $index) use ($monthlyCounts) {
            return [
                'month' => $m->locale('id')->translatedFormat('M Y'),
                'Bugs'  => (int) ($monthlyCounts->{"m{$index}"} ?? 0),
            ];
        });
    }

    private function buildSlaTimeline(Collection $items, string $timezone): array
    {
        return $items
            ->map(function (BugStatusHistory $h) use ($timezone): ?array {
                $bug = $h->bug;
                if (! $bug) return null;

                $createdAt  = $bug->created_at
                    ? Carbon::parse($bug->created_at, $timezone)
                    : null;
                $resolvedAt = $h->changed_at
                    ? Carbon::parse($h->changed_at, $timezone)
                    : null;

                if (! $createdAt || ! $resolvedAt) return null;

                $actualMinutes = max(0, (int) $createdAt->diffInMinutes($resolvedAt, false));

                $slaHours      = $bug->priority?->sla_hours;
                $targetMinutes = is_numeric($slaHours) && $slaHours > 0
                    ? (int) ($slaHours * 60)
                    : null;

                if ($targetMinutes === null) return null;

                $deltaMinutes = $targetMinutes - $actualMinutes;
                $status       = $actualMinutes <= $targetMinutes ? 'met' : 'breached';

                return [
                    'ticket'          => '#' . $bug->id,
                    'title'           => (string) ($bug->title ?? ''),
                    'date_label'      => $resolvedAt->locale('id')->translatedFormat('d M'),
                    'date_sort'       => $resolvedAt->format('Y-m-d H:i:s'),
                    'target_minutes'  => $targetMinutes,
                    'actual_minutes'  => $actualMinutes,
                    'delta_minutes'   => $deltaMinutes,
                    'status'          => $status,
                ];
            })
            ->filter()
            ->sortBy('date_sort')
            ->values()
            ->all();
    }

    private function formatDurationMinutes(int $minutes): string
    {
        $minutes  = max(0, $minutes);
        $days     = intdiv($minutes, 1440);
        $remaining = $minutes % 1440;
        $hours    = intdiv($remaining, 60);
        $mins     = $remaining % 60;

        $parts = [];
        if ($days > 0)                         $parts[] = $days . ' hari';
        if ($hours > 0)                        $parts[] = $hours . ' jam';
        if ($mins > 0 || empty($parts))        $parts[] = $mins . ' menit';

        return implode(' ', $parts);
    }

    private function resolvedBaseQuery(int $assigneeId, Carbon $fromAt, Carbon $toAt): Builder
    {
        return BugStatusHistory::query()
            ->join('bugs', 'bugs.id', '=', 'bug_status_histories.bug_id')
            ->where('bug_status_histories.new_status', 'Resolved')
            ->where('bugs.assignee_id', $assigneeId)
            ->whereNull('bugs.deleted_at')
            ->whereBetween('bug_status_histories.changed_at', [$fromAt, $toAt]);
    }

    private function resolveDateRange(Request $request, Carbon $now, string $timezone): array
    {
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
            $dateFrom,
            $dateTo,
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
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
}
