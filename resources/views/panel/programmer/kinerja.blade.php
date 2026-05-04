{{-- resources/views/programmer/kinerja.blade.php --}}
@extends('layouts.programmer')

@section('title', 'Programmer Kinerja')

@section('content')
@php
    $avgMonthly   = (int) round(collect($monthlyData)->avg('bugs') ?? 0);
    $fixedInRange = (int) $totalFixed;
    $user         = auth()->user();
    $userName     = $user?->name ?? 'Saya';
    $userAvatar   = strtoupper(substr((string) ($user?->name ?? 'P'), 0, 1));

    $formatDuration = function (?int $minutes): ?string {
        if ($minutes === null) return null;
        $d  = intdiv($minutes, 1440); $rem = $minutes % 1440;
        $hh = intdiv($rem, 60);       $mm  = $rem % 60;
        $parts = [];
        if ($d)                   $parts[] = $d  . ' hari';
        if ($hh)                  $parts[] = $hh . ' jam';
        if ($mm || empty($parts)) $parts[] = $mm . ' menit';
        return implode(' ', $parts);
    };

    $statCards = [
        [
            'label'   => 'Bug diperbaiki',
            'tooltip' => 'Jumlah event status menjadi Resolved pada rentang tanggal terpilih.',
            'value'   => number_format($fixedInRange),
            'sub'     => 'Dalam rentang terpilih',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        ],
        [
            'label'   => 'Bulan ini',
            'tooltip' => 'Jumlah event Resolved di bulan berjalan.',
            'value'   => number_format($thisMonth),
            'sub'     => 'Periode bulan berjalan',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18" /><path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />',
        ],
        [
            'label'   => 'Rata-rata bulanan',
            'tooltip' => 'Rata-rata bug resolved per bulan dari data 6 bulan terakhir.',
            'value'   => number_format($avgMonthly),
            'sub'     => 'Basis 6 bulan terakhir',
            'icon'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" /><path stroke-linecap="round" stroke-linejoin="round" d="M7 14v4m5-10v10m5-14v14" />',
        ],
    ];

    $totalMonthly   = collect($monthlyData)->sum('Bugs');
    $slaMetCount    = collect($slaTimeline)->where('status', 'met')->count();
    $slaBreachCount = collect($slaTimeline)->where('status', 'breached')->count();
    $slaTotal       = $slaMetCount + $slaBreachCount;
    $slaCompliance  = $slaTotal > 0 ? round(($slaMetCount / $slaTotal) * 100, 1) : null;

    $dateFields = [
        ['id' => 'programmer-kinerja-from', 'name' => 'from', 'label' => 'Dari Tanggal',   'value' => $dateFrom],
        ['id' => 'programmer-kinerja-to',   'name' => 'to',   'label' => 'Sampai Tanggal', 'value' => $dateTo],
    ];

    $skeletonBarHeights = [60, 85, 45, 100, 70, 55, 90, 40];
@endphp

{{-- ── Flatpickr Theme ─────────────────────────────────────────────────── --}}
<style>
    .flatpickr-day.selected,.flatpickr-day.selected:hover,
    .flatpickr-day.startRange,.flatpickr-day.startRange:hover,
    .flatpickr-day.endRange,.flatpickr-day.endRange:hover,
    .flatpickr-day.selected.prevMonthDay,.flatpickr-day.selected.nextMonthDay {
        background:#8a0b4e !important; border-color:#8a0b4e !important; color:#fff !important;
    }
    .flatpickr-day.today                          { border-color:#8a0b4e !important; color:#8a0b4e !important; }
    .flatpickr-day.today:hover                    { background:#8a0b4e !important; border-color:#8a0b4e !important; color:#fff !important; }
    .flatpickr-day:hover,.flatpickr-day:focus     { background:#f5e8ef !important; border-color:#f5e8ef !important; color:#8a0b4e !important; }
    .flatpickr-day.inRange,
    .flatpickr-day.prevMonthDay.inRange,
    .flatpickr-day.nextMonthDay.inRange           { background:#f5e8ef !important; border-color:#f5e8ef !important; box-shadow:-5px 0 0 #f5e8ef,5px 0 0 #f5e8ef !important; color:#8a0b4e !important; }
    .flatpickr-prev-month svg,.flatpickr-next-month svg { fill:#8a0b4e !important; }
    .flatpickr-prev-month:hover,.flatpickr-next-month:hover { color:#8a0b4e !important; }
    .flatpickr-current-month .flatpickr-monthDropdown-months:focus,
    .flatpickr-current-month input.cur-year:focus { outline-color:#8a0b4e !important; }
</style>

<div>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <div class="mb-4 flex items-center gap-2 text-xs">
            <a href="{{ route('programmer.dashboard') }}" class="text-slate-400 transition-colors hover:text-[#8a0b4e]">
                Dashboard
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-3 w-3 text-slate-300" aria-hidden="true">
                <path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium text-slate-600">Kinerja Saya</span>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat Kinerja Saya</h1>
                <p class="mt-1.5 text-sm text-slate-500">
                    Pantau output pekerjaan berdasarkan event bug yang telah masuk status
                    <span class="font-medium text-slate-700">Resolved</span>.
                </p>
            </div>
            <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-white px-3.5 py-2 shadow-sm">
                <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#f5e8ef] text-[#8a0b4e]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-3 w-3" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 6a.5.5 0 0 0-.5.5v5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-5a.5.5 0 0 0-.5-.5h-7Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-600">{{ $timezone }}</span>
            </div>
        </div>
    </div>

    {{-- ── Filter ───────────────────────────────────────────────────────── --}}
    <div id="programmer-kinerja-filter" class="mb-8 scroll-mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-sm">

        <div class="flex items-center border-b border-slate-100 px-6 py-4">
            <div class="flex items-center gap-2.5">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#f5e8ef] text-[#8a0b4e]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591L15.75 12.5v6.75a.75.75 0 0 1-.316.622l-3 2.25a.75.75 0 0 1-1.184-.622V12.5L4.659 7.409A2.25 2.25 0 0 1 4 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Filter</p>
                    <p class="text-[11px] text-slate-400">{{ $timezone }}</p>
                </div>
            </div>
        </div>

        <form id="programmer-kinerja-filter-form" method="GET" action="{{ route('programmer.kinerja') }}" class="p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($dateFields as $field)
                    <div>
                        <label for="{{ $field['id'] }}" class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.1em] text-slate-400">
                            {{ $field['label'] }}
                        </label>
                        <div class="relative">
                            <input
                                id="{{ $field['id'] }}" type="text"
                                name="{{ $field['name'] }}" value="{{ $field['value'] }}"
                                data-flatpickr placeholder="Pilih tanggal"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-3 pr-10 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                            />
                            <button type="button" data-flatpickr-open="{{ $field['id'] }}"
                                aria-label="Buka kalender" title="Buka kalender"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-300 transition-colors hover:text-[#8a0b4e]">
                                <x-icon name="calendar" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[11px] text-slate-400">Opsional. Kosongkan untuk melihat semua data.</p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('programmer.kinerja') }}"
                        class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 transition-all duration-200 hover:border-[#8a0b4e]/10 hover:bg-[#8a0b4e]/[0.01] hover:text-[#8a0b4e]">
                        Reset
                    </a>
                    <button type="submit" data-filter-submit
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-xl px-5 text-xs font-medium text-white transition-colors"
                        style="background-color:#8a0b4e;">
                        <svg data-filter-submit-spinner class="hidden h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v4m0 10v4m9-9h-4M7 12H3m15.364 6.364-2.829-2.828M8.465 8.465 5.636 5.636m12.728 0-2.829 2.829M8.465 15.536l-2.829 2.828" />
                        </svg>
                        <span data-filter-submit-label>Terapkan</span>
                    </button>
                </div>
            </div>
        </form>

    </div>

    {{-- ── Loading Skeleton ─────────────────────────────────────────────── --}}
    <div id="programmer-kinerja-loading-skeleton" class="mb-8 hidden space-y-6" aria-live="polite" aria-busy="true">

        {{-- Skeleton: Stats --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                    <div class="animate-pulse flex items-start justify-between gap-4">
                        <div class="flex-1 space-y-4">
                            <div class="h-2 w-24 rounded-full bg-slate-100"></div>
                            <div class="h-10 w-14 rounded-xl bg-slate-100"></div>
                            <div class="h-2 w-32 rounded-full bg-slate-100/70"></div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-100"></div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Skeleton: Chart --}}
        <div class="animate-pulse overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                <div class="space-y-2.5">
                    <div class="h-2 w-28 rounded-full bg-slate-100"></div>
                    <div class="h-4 w-52 rounded-lg bg-slate-100"></div>
                    <div class="h-2 w-36 rounded-full bg-slate-100/70"></div>
                </div>
                <div class="h-8 w-28 rounded-xl bg-slate-100"></div>
            </div>
            <div class="flex gap-5 px-6 py-3">
                <div class="h-2 w-24 rounded-full bg-slate-100/70"></div>
                <div class="h-2 w-20 rounded-full bg-slate-100/70"></div>
            </div>
            <div class="px-6 pb-6 pt-2">
                <div class="flex items-end gap-3 rounded-2xl bg-slate-50/60 px-6 pb-4 pt-6" style="height:220px">
                    @foreach ($skeletonBarHeights as $h)
                        <div class="flex-1 rounded-t-lg bg-slate-200/60" style="height:{{ $h }}%"></div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Skeleton: History --}}
        <div class="animate-pulse overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="space-y-2.5 border-b border-slate-100 px-6 py-5">
                <div class="h-2 w-28 rounded-full bg-slate-100"></div>
                <div class="h-4 w-44 rounded-lg bg-slate-100"></div>
                <div class="h-2 w-56 rounded-full bg-slate-100/70"></div>
            </div>
            <div class="divide-y divide-slate-100 px-6">
                @for ($i = 0; $i < 4; $i++)
                    <div class="py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 space-y-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-16 rounded-full bg-slate-100"></div>
                                    <div class="h-2 w-20 rounded-full bg-slate-100/70"></div>
                                </div>
                                <div class="h-3.5 w-3/5 rounded-lg bg-slate-100"></div>
                                <div class="h-2 w-2/5 rounded-full bg-slate-100/70"></div>
                            </div>
                            <div class="h-4 w-4 rounded bg-slate-100"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

    </div>

    {{-- ── Main Content ──────────────────────────────────────────────────── --}}
    <div id="programmer-kinerja-main-content">

        {{-- Stats Cards --}}
        <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

            @foreach ($statCards as $card)
                <div class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">{{ $card['label'] }}</p>
                                <span class="inline-flex h-4 w-4 cursor-help items-center justify-center rounded-full border border-slate-200 text-[9px] font-medium text-slate-400 transition-colors hover:border-[#8a0b4e] hover:text-[#8a0b4e]"
                                    title="{{ $card['tooltip'] }}">i</span>
                            </div>
                            <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $card['sub'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-colors group-hover:bg-[#f5e8ef] group-hover:text-[#8a0b4e]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" aria-hidden="true">
                                {!! $card['icon'] !!}
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Project Teratas --}}
            <div class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Project teratas</p>
                            <span class="inline-flex h-4 w-4 cursor-help items-center justify-center rounded-full border border-slate-200 text-[9px] font-medium text-slate-400 transition-colors hover:border-[#8a0b4e] hover:text-[#8a0b4e]"
                                title="Project dengan jumlah bug resolved terbanyak pada rentang terpilih.">i</span>
                        </div>
                        <p class="mt-4 truncate text-lg font-semibold tracking-tight text-slate-900">
                            {{ $topProject ?: '-' }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ $topProject ? $topProjectCount . ' bug' : 'Belum ada data' }}
                        </p>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-colors group-hover:bg-[#f5e8ef] group-hover:text-[#8a0b4e]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9A2.5 2.5 0 0 1 18.5 19h-13A2.5 2.5 0 0 1 3 16.5v-9Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9.5h8M8 13h5" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Analytics Card (Summary | Detail | SLA Delta) ──────────── --}}
        <div
            id="programmer-analytics"
            class="mb-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            data-monthly='@json($monthlyData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
            data-sla-timeline='@json($slaTimeline, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
            data-sla-met="{{ $slaMetCount }}"
            data-sla-breach="{{ $slaBreachCount }}"
            data-avg-monthly="{{ $avgMonthly }}"
            data-total-monthly="{{ $totalMonthly }}"
            data-sla-compliance="{{ $slaCompliance ?? '' }}"
            data-total-fixed="{{ $fixedInRange }}"
            data-date-range="{{ $dateFromLabel }} – {{ $dateToLabel }}"
        >
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" id="programmer-analytics-label">
                        Kinerja Insights
                    </p>
                    <p class="mt-1 text-sm font-medium text-slate-900" id="programmer-analytics-title">
                        Tren produktivitas & SLA saya
                    </p>
                    <p class="mt-1 text-sm text-slate-500" id="programmer-analytics-subtitle">
                        {{ $dateFromLabel }} – {{ $dateToLabel }}
                    </p>
                </div>
                <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1">
                    @foreach (['summary' => 'Summary', 'detail' => 'Detail', 'sla' => 'SLA Delta'] as $mode => $label)
                        <button type="button" data-analytics-mode="{{ $mode }}"
                            class="programmer-analytics-mode-btn rounded-lg px-3 py-1.5 text-xs font-medium text-slate-500 transition-colors duration-150">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="px-6 pb-6 pt-5">
                <div id="programmer-analytics-canvas" class="transition-all duration-200 ease-out" style="min-height:200px;"></div>
            </div>
        </div>

        {{-- ── Riwayat Perbaikan ───────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Riwayat Perbaikan</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $userName }}</p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $resolved->total() }} bug diperbaiki pada periode {{ $dateFromLabel }} – {{ $dateToLabel }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold text-white"
                        style="background-color:#8a0b4e;" title="{{ $userName }}">
                        {{ $userAvatar }}
                    </div>
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-medium text-slate-700">{{ $userName }}</p>
                        <p class="text-[11px] text-slate-400">Kinerja saya</p>
                    </div>
                    <div class="hidden text-slate-300 sm:block" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5">
                <div class="space-y-3">
                    @forelse ($resolved as $item)
                        @php
                            $rawTitle   = (string) ($item->bug?->title ?? '-');
                            $cleanTitle = trim(preg_replace('/\s*-\s*SLA\s+(Tepat|Lewat|Terlambat)(\s*\([^)]*\))?/iu', '', $rawTitle) ?: $rawTitle);

                            $slaHours      = $item->bug?->priority?->sla_hours;
                            $createdAt     = $item->bug?->created_at ? \Carbon\Carbon::parse($item->bug->created_at, $timezone) : null;
                            $resolvedAt    = $item->changed_at       ? \Carbon\Carbon::parse($item->changed_at, $timezone)       : null;
                            $targetMinutes = is_numeric($slaHours) && $slaHours > 0 ? (int) ($slaHours * 60) : null;
                            $actualMinutes = ($createdAt && $resolvedAt) ? max(0, (int) $createdAt->diffInMinutes($resolvedAt, false)) : null;

                            $slaStatus   = null;
                            $slaNoteText = null;
                            if ($targetMinutes !== null && $actualMinutes !== null) {
                                $delta       = $actualMinutes - $targetMinutes;
                                $slaStatus   = $delta <= 0 ? 'on_time' : 'late';
                                $slaNoteText = $slaStatus === 'late'
                                    ? 'Terlambat ' . $formatDuration($delta)
                                    : ($delta < 0 ? 'Lebih cepat ' . $formatDuration(abs($delta)) : 'Tepat waktu');
                            }

                            $slaTextClass  = match ($slaStatus) { 'on_time' => 'text-emerald-600', 'late' => 'text-rose-600', default => 'text-slate-500' };
                            $targetLabel   = $formatDuration($targetMinutes);
                            $actualLabel   = $formatDuration($actualMinutes);
                            $resolvedLabel = $item->changed_at?->timezone($timezone)->locale('id')->translatedFormat('d M Y, H:i') ?? '-';
                        @endphp

                        <a href="{{ route('programmer.bugs.show', $item->bug_id) }}"
                            class="group block rounded-2xl border border-slate-200/80 bg-white px-5 py-4 transition-colors duration-200 hover:border-[#8a0b4e]/10 hover:bg-[#8a0b4e]/[0.01] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8a0b4e]/10"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                                            {{ $item->bug->ticket ?? ('#' . $item->bug_id) }}
                                        </span>
                                        @if ($item->bug?->project?->name)
                                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] font-medium text-slate-600">
                                                {{ $item->bug->project->name }}
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="mt-2 truncate text-sm font-medium leading-snug text-slate-900 transition-colors group-hover:text-slate-700"
                                        title="{{ $cleanTitle }}">
                                        {{ $cleanTitle }}
                                    </h4>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                        <span>Selesai {{ $resolvedLabel }}</span>
                                        @if ($slaNoteText)
                                            <span class="text-slate-300" aria-hidden="true">•</span>
                                            <span class="font-medium {{ $slaTextClass }}">{{ $slaNoteText }}</span>
                                            @if ($targetLabel && $actualLabel)
                                                <span class="text-slate-400">(Target {{ $targetLabel }} • Selesai {{ $actualLabel }})</span>
                                            @endif
                                        @endif
                                    </div>

                                </div>
                                <div class="hidden shrink-0 text-slate-300 transition-colors group-hover:text-[#8a0b4e] sm:block" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6" />
                                    </svg>
                                </div>
                            </div>
                        </a>

                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/30 px-6 py-10 text-center">
                            <p class="text-sm font-medium text-slate-900">Tidak ada data di rentang ini</p>
                            <p class="mt-1 text-sm text-slate-500">Riwayat perbaikan tidak ditemukan untuk filter saat ini.</p>
                            <a href="#programmer-kinerja-filter"
                                class="mt-4 inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 transition-all duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#8a0b4e]/[0.02] hover:text-[#8a0b4e]">
                                Pilih rentang lain
                            </a>
                        </div>
                    @endforelse
                </div>

                @if ($resolved->hasPages())
                    <div class="mt-5">
                        <x-pagination :paginator="$resolved" />
                    </div>
                @endif
            </div>

        </div>

    </div>{{-- /#programmer-kinerja-main-content --}}

</div>{{-- /page wrapper --}}

{{-- =====================================================================
     SCRIPTS
     ===================================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ─────────────────────────────────────────────────────────────────────
     * SHARED HELPERS
     * ───────────────────────────────────────────────────────────────────── */
    const numFmt = new Intl.NumberFormat('id-ID');

    const escHtml = (v) => String(v ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

    const truncate = (v, len = 72) => {
        const s = String(v ?? '');
        return s.length > len ? `${s.slice(0, len - 1)}…` : s;
    };

    const toNum = (v) => { const n = Number(v ?? 0); return Number.isFinite(n) ? n : 0; };

    const fmtMinutes = (v) => {
        const total = Math.max(0, Math.round(toNum(v)));
        const d = Math.floor(total / 1440);
        const h = Math.floor((total % 1440) / 60);
        const m = total % 60;
        const parts = [];
        if (d > 0)                       parts.push(`${numFmt.format(d)} hari`);
        if (h > 0)                       parts.push(`${numFmt.format(h)} jam`);
        if (m > 0 || parts.length === 0) parts.push(`${numFmt.format(m)} menit`);
        return parts.join(' ');
    };

    const fmtSigned = (v) => {
        const m = Math.round(toNum(v));
        return m === 0 ? '0 menit' : `${m > 0 ? '+' : '-'}${fmtMinutes(Math.abs(m))}`;
    };

    const avg = (arr) => arr.length ? Math.round(arr.reduce((s, v) => s + v, 0) / arr.length) : null;

    /* Buat tooltip dan bind ke semua [data-chart-tooltip] dalam surface */
    const bindTooltip = (surface) => {
        const targets = surface?.querySelectorAll('[data-chart-tooltip]');
        if (!surface || !targets?.length) return;

        const tip = Object.assign(document.createElement('div'), {
            className: 'pointer-events-none absolute z-30 hidden rounded-xl border border-slate-200/80 bg-white px-3.5 py-3 text-[11px] text-slate-600 shadow-sm',
        });
        tip.style.cssText = 'max-width:220px;min-width:160px;';
        surface.appendChild(tip);

        let active = null;

        const place = (target) => {
            const sr = surface.getBoundingClientRect();
            const tr = target.getBoundingClientRect();
            tip.style.visibility = 'hidden';
            tip.classList.remove('hidden');
            const tw = tip.offsetWidth || 200;
            const th = tip.offsetHeight || 100;
            tip.classList.add('hidden');
            tip.style.visibility = '';
            const GAP  = 10;
            const left = Math.max(GAP, Math.min(sr.width - tw - GAP, (tr.left + tr.width / 2) - sr.left - tw / 2));
            let   top  = (tr.top - sr.top) - th - GAP;
            if (top < GAP) top = (tr.bottom - sr.top) + GAP;
            tip.style.left = `${left}px`;
            tip.style.top  = `${Math.max(GAP, Math.min(sr.height - th - GAP, top))}px`;
        };

        const show = (target) => {
            const { tooltipTitle: t, tooltipMeta: m, tooltipNote: n, tooltipValue: v, tooltipAccent: a = '#0f172a' } = target.dataset;
            tip.innerHTML = `
                <div class="min-w-[160px]">
                    ${m ? `<p class="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-400">${escHtml(m)}</p>` : ''}
                    ${t ? `<p class="mt-0.5 text-xs font-medium text-slate-900">${escHtml(t)}</p>` : ''}
                    ${n ? `<p class="mt-1 text-[11px] leading-relaxed text-slate-500">${escHtml(n)}</p>` : ''}
                    ${v ? `<div class="mt-1.5 flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full" style="background:${a}"></span><p class="text-[11px] leading-relaxed text-slate-600">${escHtml(v)}</p></div>` : ''}
                </div>`;
            tip.classList.remove('hidden');
            place(target);
            if (active && active !== target) active.setAttribute('fill-opacity', active.dataset.baseOpacity || '0.65');
            active = target;
            target.setAttribute('fill-opacity', '1');
        };

        const hide = () => {
            tip.classList.add('hidden');
            if (active) { active.setAttribute('fill-opacity', active.dataset.baseOpacity || '0.65'); active = null; }
        };

        targets.forEach((t) => {
            t.addEventListener('mouseenter', () => show(t));
            t.addEventListener('mouseleave', hide);
            t.addEventListener('focus',      () => show(t));
            t.addEventListener('blur',       hide);
        });
        surface.addEventListener('mouseleave', hide);
    };

    /* ─────────────────────────────────────────────────────────────────────
     * 1. FILTER FORM — loading state
     * ───────────────────────────────────────────────────────────────────── */
    (() => {
        const form        = document.getElementById('programmer-kinerja-filter-form');
        const skeleton    = document.getElementById('programmer-kinerja-loading-skeleton');
        const mainContent = document.getElementById('programmer-kinerja-main-content');
        const submitBtn   = form?.querySelector('[data-filter-submit]');
        const spinner     = form?.querySelector('[data-filter-submit-spinner]');
        const label       = form?.querySelector('[data-filter-submit-label]');

        if (!form || !skeleton || !mainContent || !submitBtn || !spinner || !label) return;

        let busy = false;

        form.addEventListener('submit', (e) => {
            if (busy) { e.preventDefault(); return; }
            e.preventDefault();
            busy = true;

            submitBtn.setAttribute('disabled', 'disabled');
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
            spinner.classList.remove('hidden');
            label.textContent = 'Memuat...';

            mainContent.classList.add('hidden');
            skeleton.classList.remove('hidden');

            requestAnimationFrame(() => requestAnimationFrame(() => form.submit()));
        });
    })();

    /* ─────────────────────────────────────────────────────────────────────
     * 2. ANALYTICS CARD — Summary | Detail | SLA Delta
     * ───────────────────────────────────────────────────────────────────── */
    (() => {
        const wrapper     = document.getElementById('programmer-analytics');
        const canvas      = document.getElementById('programmer-analytics-canvas');
        const labelEl     = document.getElementById('programmer-analytics-label');
        const titleEl     = document.getElementById('programmer-analytics-title');
        const subtitleEl  = document.getElementById('programmer-analytics-subtitle');
        const modeButtons = wrapper?.querySelectorAll('[data-analytics-mode]');
        if (!wrapper || !canvas) return;

        /* ── Parse config ── */
        let monthlyData = [];
        try { monthlyData = JSON.parse(wrapper.dataset.monthly || '[]'); } catch { monthlyData = []; }

        let slaRows = [];
        try { slaRows = JSON.parse(wrapper.dataset.slaTimeline || '[]'); } catch { slaRows = []; }

        const slaMetCount    = parseInt(wrapper.dataset.slaMet       || '0', 10);
        const slaBreachCount = parseInt(wrapper.dataset.slaBreach    || '0', 10);
        const avgMonthly     = parseInt(wrapper.dataset.avgMonthly   || '0', 10);
        const slaCompliance  = wrapper.dataset.slaCompliance !== ''
            ? parseFloat(wrapper.dataset.slaCompliance) : null;
        const slaTotal       = slaMetCount + slaBreachCount;
        const dateRange      = wrapper.dataset.dateRange || '';

        /* Normalise monthly rows */
        const rows = monthlyData.map((r) => ({
            month: String(r.month ?? ''),
            bugs:  Math.max(0, parseInt(r.Bugs ?? r.bugs ?? 0, 10)),
        }));

        /* Header text per mode */
        const headerByMode = {
            summary: { label: 'Kinerja Insights',  title: 'Tren produktivitas & SLA saya',      showSub: true },
            detail:  { label: 'Kinerja Insights',  title: `Ringkasan performa · ${dateRange}`,  showSub: false },
            sla:     { label: 'SLA Delta Chart',   title: 'Margin penyelesaian per tiket',       showSub: true },
        };

        /* Transition helper */
        const transition = (fn) => {
            canvas.style.opacity   = '0';
            canvas.style.transform = 'translateY(4px)';
            setTimeout(() => {
                fn();
                void canvas.offsetHeight;
                canvas.style.opacity   = '1';
                canvas.style.transform = 'translateY(0)';
            }, 120);
        };

        /* ── Empty state helper ── */
        const emptyHtml = (title, sub) => `
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                <p class="text-sm font-medium text-slate-900">${escHtml(title)}</p>
                <p class="mt-1 text-sm text-slate-500">${escHtml(sub)}</p>
            </div>`;

        /* ── renderSummary: horizontal bar per bulan ── */
        const renderSummary = () => {
            if (!rows.length) { canvas.innerHTML = emptyHtml('Belum ada data bulanan', 'Data akan muncul setelah ada bug yang diselesaikan.'); return; }

            const maxBugs      = Math.max(1, ...rows.map((r) => r.bugs));
            const totalBugs    = rows.reduce((s, r) => s + r.bugs, 0);
            const activeMonths = rows.filter((r) => r.bugs > 0).length;
            const peakMonth    = [...rows].sort((a, b) => b.bugs - a.bugs)[0] ?? null;

            const rowsHtml = rows.map((row) => {
                const pct      = Math.max(row.bugs > 0 ? 8 : 0, (row.bugs / maxBugs) * 100);
                const isAbove  = row.bugs > avgMonthly;
                const barColor = isAbove ? '#8a0b4e' : '#c4799a';
                const tipValue = `${numFmt.format(row.bugs)} bug${isAbove ? ' · Di atas rata-rata' : row.bugs < avgMonthly ? ' · Di bawah rata-rata' : ' · Tepat rata-rata'}`;
                return `
                    <div class="grid grid-cols-[56px_minmax(0,1fr)_40px] items-center gap-4 px-4 py-3">
                        <p class="text-[11px] font-medium text-slate-500">${escHtml(row.month)}</p>
                        <div class="relative">
                            <div class="h-7 overflow-hidden rounded-lg bg-slate-100">
                                <div class="flex h-full items-center justify-center rounded-lg text-[10px] font-medium text-white transition-all duration-300"
                                    style="width:${pct}%;background-color:${barColor};"
                                    data-chart-tooltip
                                    data-tooltip-title="${escHtml(row.month)}"
                                    data-tooltip-meta="Bug Diselesaikan"
                                    data-tooltip-value="${escHtml(tipValue)}"
                                    data-tooltip-accent="${barColor}"
                                    data-base-opacity="1" tabindex="0"
                                >${pct >= 18 ? numFmt.format(row.bugs) : ''}</div>
                            </div>
                            ${avgMonthly > 0 ? `<div class="pointer-events-none absolute inset-y-0" style="left:${Math.min(99,(avgMonthly/maxBugs)*100)}%;border-left:1.5px dashed #cbd5e1;"></div>` : ''}
                        </div>
                        <p class="text-right text-[11px] font-medium ${isAbove ? 'text-[#8a0b4e]' : 'text-slate-400'}">${numFmt.format(row.bugs)}</p>
                    </div>`;
            }).join('');

            canvas.innerHTML = `
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] text-slate-500">
                        ${totalBugs > 0
                            ? `<span><strong class="font-semibold text-slate-700">${numFmt.format(totalBugs)}</strong> bug diselesaikan</span>`
                            : `<span class="text-slate-400">Belum ada bug diselesaikan pada periode ini</span>`}
                        ${peakMonth?.bugs > 0 ? `<span class="text-slate-300">·</span><span>Output tertinggi <strong class="font-semibold text-slate-700">${escHtml(peakMonth.month)}</strong> sebanyak ${numFmt.format(peakMonth.bugs)} bug</span>` : ''}
                        ${rows.length > 0 ? `<span class="text-slate-300">·</span><span><strong class="font-semibold text-slate-700">${activeMonths}</strong> dari ${rows.length} bulan memiliki output</span>` : ''}
                        ${avgMonthly > 0 ? `<span class="text-slate-300">·</span><span>Rata-rata <strong class="font-semibold text-slate-700">${numFmt.format(avgMonthly)}</strong> bug per bulan</span>` : ''}
                    </div>
                    <div data-chart-surface class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white">
                        <div class="divide-y divide-slate-100">${rowsHtml}</div>
                    </div>
                </div>`;

            bindTooltip(canvas.querySelector('[data-chart-surface]'));
        };

        /* ── renderDetail: grid insight cards ── */
        const renderDetail = () => {
            const metActuals      = slaRows.filter((r) => r.status === 'met').map((r) => toNum(r.actual_minutes));
            const breachedActuals = slaRows.filter((r) => r.status === 'breached').map((r) => toNum(r.actual_minutes));
            const allActuals      = slaRows.map((r) => toNum(r.actual_minutes)).filter((v) => v > 0);
            const avgActual       = avg(allActuals);
            const avgMet          = avg(metActuals);
            const avgBreached     = avg(breachedActuals);
            const slaRate         = slaTotal > 0 ? ((slaMetCount / slaTotal) * 100).toFixed(1) : null;
            const fastestMet      = [...slaRows].filter((r) => r.status === 'met').sort((a, b) => toNum(b.delta_minutes) - toNum(a.delta_minutes))[0] ?? null;
            const worstBreach     = [...slaRows].filter((r) => r.status === 'breached').sort((a, b) => toNum(a.delta_minutes) - toNum(b.delta_minutes))[0] ?? null;

            const slaColor = slaRate === null ? '#94a3b8'
                : parseFloat(slaRate) >= 80 ? '#10b981'
                : parseFloat(slaRate) >= 60 ? '#f59e0b' : '#f43f5e';

            const detailItems = [
                {
                    icon:    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                    label:   'SLA Compliance',
                    primary: slaTotal === 0 ? '—' : `${slaRate}%`,
                    color:   slaColor,
                    sub:     slaTotal === 0
                        ? 'Belum ada tiket dengan target SLA terukur.'
                        : `${numFmt.format(slaMetCount)} tepat waktu · ${numFmt.format(slaBreachCount)} terlambat dari ${numFmt.format(slaTotal)} tiket.`,
                },
                {
                    icon:    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                    label:   'Rata-rata Waktu Selesai',
                    primary: avgActual !== null ? fmtMinutes(avgActual) : '—',
                    color:   '#8a0b4e',
                    sub:     avgActual !== null
                        ? `Tepat waktu rata-rata ${avgMet !== null ? fmtMinutes(avgMet) : '—'} · Terlambat rata-rata ${avgBreached !== null ? fmtMinutes(avgBreached) : '—'}`
                        : 'Belum ada data waktu penyelesaian.',
                },
                {
                    icon:    '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/>',
                    label:   'Penyelesaian Tercepat',
                    primary: fastestMet ? fmtMinutes(toNum(fastestMet.actual_minutes)) : '—',
                    color:   '#10b981',
                    sub:     fastestMet
                        ? `${escHtml(fastestMet.ticket)} · ${escHtml(fastestMet.date_label)} · lebih cepat ${fmtMinutes(Math.abs(toNum(fastestMet.delta_minutes)))} dari target`
                        : 'Belum ada tiket yang selesai tepat waktu.',
                },
                {
                    icon:    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75h.007v.008H12v-.008Z"/>',
                    label:   'Pelanggaran SLA Terparah',
                    primary: worstBreach ? fmtMinutes(Math.abs(toNum(worstBreach.delta_minutes))) : '—',
                    color:   worstBreach ? '#f43f5e' : '#94a3b8',
                    sub:     worstBreach
                        ? `${escHtml(worstBreach.ticket)} · ${escHtml(worstBreach.date_label)} · terlambat ${fmtMinutes(Math.abs(toNum(worstBreach.delta_minutes)))} dari target`
                        : 'Tidak ada pelanggaran SLA pada periode ini.',
                },
            ];

            canvas.innerHTML = `
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    ${detailItems.map((item) => `
                        <div class="rounded-2xl border border-slate-200/80 bg-white px-5 py-4">
                            <p class="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-400">${escHtml(item.label)}</p>
                            <p class="mt-2 text-2xl font-semibold tabular-nums tracking-tight" style="color:${item.color};">${item.primary}</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">${item.sub}</p>
                        </div>`).join('')}
                </div>`;
        };

        /* ── renderSla: vertical bar chart ── */
        const renderSla = () => {
            if (!Array.isArray(slaRows) || slaRows.length === 0) {
                canvas.innerHTML = emptyHtml('Belum ada tiket terukur SLA', 'Grafik akan tampil saat ada tiket dengan target SLA valid.');
                return;
            }

            const normalized = slaRows.map((r, i) => {
                const target = toNum(r.target_minutes);
                const actual = toNum(r.actual_minutes);
                const delta  = Number.isFinite(Number(r.delta_minutes)) ? Number(r.delta_minutes) : (target - actual);
                return {
                    index: i, ticket: String(r.ticket ?? `#${i+1}`),
                    title: String(r.title ?? ''), dateLabel: String(r.date_label ?? ''),
                    target, actual, delta,
                    status: String(r.status ?? (delta >= 0 ? 'met' : 'breached')),
                };
            });

            /* Domain */
            const rawMax    = Math.max(30, ...normalized.map((r) => Math.abs(r.delta)));
            const steps     = [15, 30, 60, 120, 180, 240, 360, 480, 720, 1440];
            let   tickStep  = steps[steps.length - 1];
            for (const s of steps) { if (Math.ceil(rawMax / s) <= 6) { tickStep = s; break; } }
            const domainMax = Math.max(tickStep, Math.ceil(rawMax / tickStep) * tickStep);

            /* Dimensions */
            const count   = normalized.length;
            const minSlot = count <= 8 ? 64 : count <= 16 ? 48 : count <= 28 ? 36 : 28;
            const canvasW = Math.floor(canvas.getBoundingClientRect().width || 680);
            const W       = Math.max(58 + 22 + count * minSlot, canvasW);
            const H       = 300;
            const mg      = { top: 24, right: 22, bottom: 52, left: 58 };
            const innerW  = W - mg.left - mg.right;
            const innerH  = H - mg.top  - mg.bottom;
            const edgePad = count === 1 ? 0 : Math.max(12, Math.min(20, innerW * 0.025));
            const plotW   = count === 1 ? innerW : Math.max(1, innerW - edgePad * 2);
            const slotW   = count === 1 ? innerW : plotW / Math.max(1, count - 1);
            const maxBar  = count <= 6 ? 28 : count <= 12 ? 22 : count <= 20 ? 16 : count <= 30 ? 12 : 8;
            const barW    = Math.max(6, Math.min(maxBar, slotW * 0.45));

            const xOf   = (i) => count === 1 ? mg.left + innerW / 2 : mg.left + edgePad + (plotW * i) / Math.max(1, count - 1);
            const yOf   = (v) => mg.top + ((domainMax - v) / (domainMax * 2)) * innerH;
            const baseY = yOf(0);

            const buildPath = ({ bx, bh, pos }) => {
                if (bh <= 0) return '';
                const r = Math.min(5, barW / 2, bh);
                if (pos) {
                    const ty = baseY - bh;
                    return `M ${bx} ${baseY} L ${bx} ${ty+r} Q ${bx} ${ty} ${bx+r} ${ty} L ${bx+barW-r} ${ty} Q ${bx+barW} ${ty} ${bx+barW} ${ty+r} L ${bx+barW} ${baseY} Z`;
                }
                const by = baseY + bh;
                return `M ${bx} ${baseY} L ${bx+barW} ${baseY} L ${bx+barW} ${by-r} Q ${bx+barW} ${by} ${bx+barW-r} ${by} L ${bx+r} ${by} Q ${bx} ${by} ${bx} ${by-r} L ${bx} ${baseY} Z`;
            };

            /* Ticks */
            const ticksSvg = [];
            for (let v = domainMax; v >= -domainMax; v -= tickStep) {
                const yp = yOf(v); const isBase = v === 0;
                ticksSvg.push(`
                    <g>
                        <line x1="${mg.left}" y1="${yp}" x2="${W-mg.right}" y2="${yp}"
                            stroke="${isBase ? '#cbd5e1' : '#f1f5f9'}" stroke-width="${isBase ? '0.75' : '0.5'}"
                            ${!isBase ? 'stroke-dasharray="2 3"' : ''} />
                        <text x="${mg.left-10}" y="${yp+4}" text-anchor="end"
                            fill="${isBase ? '#94a3b8' : '#cbd5e1'}"
                            font-size="10" font-family="system-ui,-apple-system,sans-serif"
                        >${isBase ? '0' : fmtSigned(v)}</text>
                    </g>`);
            }

            /* Bars */
            const needRotate = slotW < 44;
            const lblSize    = count > 24 ? 8 : count > 16 ? 9 : 10;
            const lblY       = H - (needRotate ? 6 : 14);

            const barsSvg = normalized.map((row) => {
                const isMet = row.status === 'met' || row.delta >= 0;
                const cx    = xOf(row.index);
                const rawH  = Math.abs(yOf(row.delta) - baseY);
                const bh    = Math.max(rawH, row.delta === 0 ? 3 : Math.abs(row.delta) < 10 ? 6 : 0);
                const bx    = cx - barW / 2;
                const fill  = isMet ? '#10b981' : '#f43f5e';
                const tip   = `Deviasi ${fmtSigned(row.delta)} · Target ${fmtMinutes(row.target)} · Aktual ${fmtMinutes(row.actual)}`;
                const lbl   = needRotate
                    ? `<text x="${cx}" y="${lblY}" text-anchor="end" fill="#94a3b8" font-size="${lblSize}" font-family="system-ui,-apple-system,sans-serif" transform="rotate(-45 ${cx} ${lblY})">${escHtml(row.dateLabel)}</text>`
                    : `<text x="${cx}" y="${lblY}" text-anchor="middle" fill="#94a3b8" font-size="${lblSize}" font-family="system-ui,-apple-system,sans-serif">${escHtml(row.dateLabel)}</text>`;
                return `
                    <g>
                        <path d="${buildPath({ bx, bh, pos: isMet })}" fill="${fill}" fill-opacity="0.65"
                            data-chart-tooltip
                            data-tooltip-title="${escHtml(row.ticket)}"
                            data-tooltip-meta="${escHtml(row.dateLabel)}"
                            data-tooltip-note="${escHtml(truncate(row.title || '-'))}"
                            data-tooltip-value="${escHtml(tip)}"
                            data-tooltip-accent="${fill}"
                            data-base-opacity="0.65"
                            tabindex="0" class="cursor-pointer transition-all duration-150"
                            aria-label="${escHtml(`${row.ticket}: ${tip}`)}"
                        />
                        ${lbl}
                    </g>`;
            }).join('');

            const needScroll = W > canvasW;
            canvas.innerHTML = `
                <div data-chart-surface class="relative ${needScroll ? 'overflow-x-auto' : ''}">
                    <svg viewBox="0 0 ${W} ${H}" ${needScroll ? `width="${W}" height="${H}"` : ''}
                        role="img" aria-label="SLA Delta Chart"
                        class="${needScroll ? 'block' : 'block h-auto w-full'}"
                    >${ticksSvg.join('')}${barsSvg}</svg>
                </div>`;

            bindTooltip(canvas.querySelector('[data-chart-surface]'));
        };

        /* ── Mode switcher ── */
        const renderMap = { summary: renderSummary, detail: renderDetail, sla: renderSla };

        const setMode = (mode) => {
            modeButtons?.forEach((btn) => {
                const isActive = btn.dataset.analyticsMode === mode;
                btn.classList.toggle('text-white',     isActive);
                btn.classList.toggle('shadow-sm',      isActive);
                btn.classList.toggle('text-slate-500', !isActive);
                btn.style.backgroundColor = isActive ? '#8a0b4e' : '';
            });

            const h = headerByMode[mode] ?? headerByMode.summary;
            if (labelEl)    labelEl.textContent    = h.label;
            if (titleEl)    titleEl.textContent    = h.title;
            if (subtitleEl) subtitleEl.textContent = h.showSub ? dateRange : '';

            transition(renderMap[mode] ?? renderSummary);
        };

        modeButtons?.forEach((btn) => btn.addEventListener('click', () => setMode(btn.dataset.analyticsMode)));
        setMode('summary');
    })();

}); // DOMContentLoaded
</script>

@endsection