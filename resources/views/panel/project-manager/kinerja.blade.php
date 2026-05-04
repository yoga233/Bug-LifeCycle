@extends('layouts.project-manager')

@section('title', 'Kinerja Programmer')

@section('content')

<style>
    /* ─── Flatpickr: Selected day ─── */
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange,
    .flatpickr-day.endRange:hover,
    .flatpickr-day.selected.prevMonthDay,
    .flatpickr-day.selected.nextMonthDay {
        background: #8a0b4e !important;
        border-color: #8a0b4e !important;
        color: #ffffff !important;
    }

    /* ─── Flatpickr: Today (ring only) ─── */
    .flatpickr-day.today {
        border-color: #8a0b4e !important;
        color: #8a0b4e !important;
    }
    .flatpickr-day.today:hover {
        background: #8a0b4e !important;
        border-color: #8a0b4e !important;
        color: #ffffff !important;
    }

    /* ─── Flatpickr: Hover & focus any day ─── */
    .flatpickr-day:hover,
    .flatpickr-day:focus {
        background: #f5e8ef !important;
        border-color: #f5e8ef !important;
        color: #8a0b4e !important;
    }

    /* ─── Flatpickr: In-range ─── */
    .flatpickr-day.inRange,
    .flatpickr-day.prevMonthDay.inRange,
    .flatpickr-day.nextMonthDay.inRange {
        background: #f5e8ef !important;
        border-color: #f5e8ef !important;
        box-shadow: -5px 0 0 #f5e8ef, 5px 0 0 #f5e8ef !important;
        color: #8a0b4e !important;
    }

    /* ─── Flatpickr: Navigation arrows ─── */
    .flatpickr-prev-month svg,
    .flatpickr-next-month svg {
        fill: #8a0b4e !important;
    }
    .flatpickr-prev-month:hover,
    .flatpickr-next-month:hover {
        color: #8a0b4e !important;
    }

    /* ─── Flatpickr: Month & year focus ─── */
    .flatpickr-current-month .flatpickr-monthDropdown-months:focus,
    .flatpickr-current-month input.cur-year:focus {
        outline-color: #8a0b4e !important;
    }

    /* ─── PM Primary Colors ─── */
    .pm-icon-btn:hover svg,
    .pm-icon-btn:focus svg {
        color: #8a0b4e !important;
    }

    .pm-info-badge:hover {
        border-color: #8a0b4e !important;
        color: #8a0b4e !important;
    }

    .pm-card-icon-wrap {
        transition: background-color 0.2s, color 0.2s;
    }

    .group:hover .pm-card-icon-wrap {
        background-color: #f5e8ef !important;
        color: #8a0b4e !important;
    }

    .pm-filter-icon-wrap {
        background-color: #f5e8ef !important;
        color: #8a0b4e !important;
    }

    .pm-calendar-btn:hover {
        color: #8a0b4e !important;
    }

    .pm-chart-mode-btn-active {
        background-color: #8a0b4e !important;
        color: #ffffff !important;
    }

    .pm-chart-mode-btn-inactive {
        background-color: transparent !important;
        color: #64748b !important;
    }

    .pm-chart-mode-btn-inactive:hover {
        background-color: #f5e8ef !important;
        color: #8a0b4e !important;
    }
</style>

{{-- ============================================================
     PHP Variables
     ============================================================ --}}
@php
    $titleName           = $selectedProgrammer?->name ?: 'Semua Programmer';
    $hasProgrammerFilter = (bool) $selectedProgrammer;
    $slaSummary          = is_array($slaSummary ?? null) ? $slaSummary : null;

    $formatDurationMinutes = function ($value): ?string {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $minutes   = max(0, (int) round((float) $value));
        $days      = intdiv($minutes, 1440);
        $remaining = $minutes % 1440;
        $hours     = intdiv($remaining, 60);
        $mins      = $remaining % 60;

        $parts = [];
        if ($days > 0)  $parts[] = $days  . ' hari';
        if ($hours > 0) $parts[] = $hours . ' jam';
        if ($mins > 0 || empty($parts)) $parts[] = $mins . ' menit';

        return implode(' ', $parts);
    };
@endphp

{{-- ============================================================
     1. Page Header
     ============================================================ --}}
<div class="mb-8">
    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-xs">
        <a href="{{ route('pm.dashboard') }}" class="text-slate-400 transition-colors hover:text-slate-700">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-3 w-3 text-slate-300" aria-hidden="true">
            <path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium text-slate-600">Kinerja Programmer</span>
    </div>

    {{-- Title & Description --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kinerja Programmer</h1>
            <p class="mt-1.5 text-sm text-slate-500">Pantau output per programmer berdasarkan bug yang sudah diselesaikan.</p>
        </div>

        {{-- Quick date indicator --}}
        <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-white px-3.5 py-2 shadow-sm">
            <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#f5e8ef] text-[#8a0b4e]">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-3 w-3" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 6a.5.5 0 0 0-.5.5v5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-5a.5.5 0 0 0-.5-.5h-7Z" clip-rule="evenodd" />
                </svg>
            </div>
            <span class="text-xs font-medium text-slate-600">{{ $periodLabel }}</span>
        </div>
    </div>
</div>

{{-- ============================================================
     2. Filter Section
     ============================================================ --}}
<div id="pm-kinerja-filter" class="mb-8 scroll-mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
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

    {{-- Form --}}
    <form
        id="pm-kinerja-filter-form"
        method="GET"
        action="{{ route('pm.kinerja') }}"
        class="p-6"
    >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Programmer --}}
            <div>
                <label class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.1em] text-slate-400">
                    Programmer
                </label>
                <x-pm.filter-dropdown
                    name="assignee_id"
                    :items="$programmers->map(fn($p) => ['value' => (string) $p->id, 'label' => (string) $p->name])->values()->all()"
                    :selected="(string) ($filters['assignee_id'] ?? '')"
                    placeholder="Semua Programmer"
                    :searchable="true"
                />
            </div>

            {{-- Dari Tanggal --}}
            <div>
                <label
                    for="pm-kinerja-from"
                    class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.1em] text-slate-400"
                >
                    Dari Tanggal
                </label>
                <div class="relative">
                    <input
                        id="pm-kinerja-from"
                        type="text"
                        name="from"
                        value="{{ $dateFrom }}"
                        data-flatpickr
                        placeholder="Pilih tanggal"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-3 pr-10 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    />
                    <button
                        type="button"
                        data-flatpickr-open="pm-kinerja-from"
                        class="pm-calendar-btn absolute inset-y-0 right-0 flex items-center pr-3 text-slate-300 transition-colors"
                        aria-label="Buka kalender"
                        title="Buka kalender"
                    >
                        <x-icon name="calendar" class="h-4 w-4" />
                    </button>
                </div>
            </div>

            {{-- Sampai Tanggal --}}
            <div>
                <label
                    for="pm-kinerja-to"
                    class="mb-1.5 block text-[11px] font-medium uppercase tracking-[0.1em] text-slate-400"
                >
                    Sampai Tanggal
                </label>
                <div class="relative">
                    <input
                        id="pm-kinerja-to"
                        type="text"
                        name="to"
                        value="{{ $dateTo }}"
                        data-flatpickr
                        placeholder="Pilih tanggal"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-3 pr-10 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                    />
                    <button
                        type="button"
                        data-flatpickr-open="pm-kinerja-to"
                        class="pm-calendar-btn absolute inset-y-0 right-0 flex items-center pr-3 text-slate-300 transition-colors"
                        aria-label="Buka kalender"
                        title="Buka kalender"
                    >
                        <x-icon name="calendar" class="h-4 w-4" />
                    </button>
                </div>
            </div>

        </div>

        {{-- Actions --}}
        <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            {{-- Hint --}}
            <p class="text-[11px] text-slate-400">
                Opsional. Kosongkan untuk melihat semua data.
            </p>

            {{-- Buttons --}}
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('pm.kinerja') }}"
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 transition-all duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#f5e8ef] hover:text-[#8a0b4e]"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    data-filter-submit
                    class="inline-flex h-9 items-center justify-center rounded-xl px-5 text-xs font-medium text-white transition-colors"
                    style="background-color: #8a0b4e;"
                    onmouseover="this.style.backgroundColor='#6d0940'"
                    onmouseout="this.style.backgroundColor='#8a0b4e'"
                >
                    <span data-filter-submit-label>Terapkan</span>
                </button>
            </div>

        </div>
    </form>
</div>

{{-- ============================================================
     3. Loading Skeleton
     ============================================================ --}}
<div id="pm-kinerja-loading-skeleton" class="mb-8 hidden space-y-6" aria-live="polite" aria-busy="true">

    {{-- Skeleton: Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="animate-pulse">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 space-y-4">
                            <div class="h-2 w-24 rounded-full bg-slate-100"></div>
                            <div class="h-10 w-14 rounded-xl bg-slate-100"></div>
                            <div class="h-2 w-32 rounded-full bg-slate-100/70"></div>
                        </div>
                        <div class="h-10 w-10 rounded-xl bg-slate-100"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Skeleton: Chart --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="animate-pulse">
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
                <div class="h-2 w-32 rounded-full bg-slate-100/70"></div>
            </div>
            <div class="px-6 pb-6 pt-2">
                <div class="flex items-end gap-3 rounded-2xl bg-slate-50/60 px-6 pb-4 pt-6" style="height: 220px;">
                    @foreach ([60, 85, 45, 100, 70, 55, 90, 40] as $h)
                        <div class="flex-1 rounded-t-lg bg-slate-200/60" style="height: {{ $h }}%"></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Skeleton: History List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="animate-pulse">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="space-y-2.5">
                    <div class="h-2 w-28 rounded-full bg-slate-100"></div>
                    <div class="h-4 w-44 rounded-lg bg-slate-100"></div>
                    <div class="h-2 w-56 rounded-full bg-slate-100/70"></div>
                </div>
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
</div>

{{-- ============================================================
     Main Content
     ============================================================ --}}
<div id="pm-kinerja-main-content">

    {{-- ============================================================
         4. Stats Cards
         ============================================================ --}}
    <div class="mb-8 grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Bug Diperbaiki --}}
        <div class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                            Bug diperbaiki
                        </p>
                        <span
                            class="pm-info-badge inline-flex h-4 w-4 cursor-help items-center justify-center rounded-full border border-slate-200 text-[9px] font-medium text-slate-400 transition-colors"
                            title="Jumlah event status menjadi Resolved pada rentang tanggal yang dipilih."
                        >i</span>
                    </div>
                    <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">
                        {{ number_format($fixedInRange) }}
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Dalam rentang terpilih
                    </p>
                </div>
                <div class="pm-card-icon-wrap flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Keseluruhan --}}
        <div class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                            Total keseluruhan
                        </p>
                    </div>
                    <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">
                        {{ number_format($totalFixed) }}
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Akumulasi semua waktu
                    </p>
                </div>
                <div class="pm-card-icon-wrap flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 14v4m5-10v10m5-14v14" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Bulan Ini --}}
        <div class="group rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                            Bulan ini
                        </p>
                        <span
                            class="pm-info-badge inline-flex h-4 w-4 cursor-help items-center justify-center rounded-full border border-slate-200 text-[9px] font-medium text-slate-400 transition-colors"
                            title="Jumlah event Resolved di bulan berjalan, terlepas dari rentang filter tanggal."
                        >i</span>
                    </div>
                    <p class="mt-4 text-4xl font-semibold tracking-tight tabular-nums text-slate-900">
                        {{ number_format($thisMonth) }}
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Periode bulan berjalan
                    </p>
                </div>
                <div class="pm-card-icon-wrap flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4.5 w-4.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- --------------------------------------------------------
         5. Performance Chart
         -------------------------------------------------------- --}}
    @unless ($hasProgrammerFilter)
        @php
            $currentTotal    = (int) ($chartTotals['current'] ?? 0);
            $programmerCount = is_countable($chart ?? null) ? count($chart) : 0;
            $avgPerProgrammer = $programmerCount > 0 ? round($currentTotal / $programmerCount, 1) : 0;
        @endphp

        <div
            id="pm-performance-analytics"
            class="mb-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            data-chart='@json($chart, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
            data-selected-programmer-id="{{ $selectedProgrammer?->id ?? '' }}"
        >
            {{-- Header --}}
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                        Programmer Insights
                    </p>
                    <p class="mt-1 text-sm font-medium text-slate-900">
                        Produktivitas & SLA per Programmer
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $periodLabel }}
                    </p>
                </div>

                {{-- Toggle Summary / Detail --}}
                <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1">
                    <button
                        type="button"
                        data-chart-mode="summary"
                        class="pm-chart-mode-btn rounded-lg px-3 py-1.5 text-xs font-medium transition-colors duration-150"
                    >
                        Summary
                    </button>
                    <button
                        type="button"
                        data-chart-mode="detail"
                        class="pm-chart-mode-btn rounded-lg px-3 py-1.5 text-xs font-medium transition-colors duration-150"
                    >
                        Detail
                    </button>
                </div>
            </div>

            {{-- Meta Summary --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 px-6 py-4 text-xs text-slate-500">
                <span>Total {{ number_format($currentTotal) }} bug selesai</span>
                <span>{{ number_format($programmerCount) }} programmer</span>
                <span>Rata-rata {{ number_format($avgPerProgrammer, 1) }} bug / programmer</span>
            </div>

            {{-- Canvas --}}
            <div class="px-6 pb-6 pt-6">
                <div
                    id="pm-performance-chart-canvas"
                    class="transition-all duration-200 ease-out"
                    style="min-height: 220px;"
                ></div>
            </div>
        </div>
    @endunless

    {{-- ============================================================
         SLA Snapshot
         ============================================================ --}}
    @if ($hasProgrammerFilter)
        @php
            $onTimeCount           = (int)   ($slaSummary['on_time_count']            ?? 0);
            $lateCount             = (int)   ($slaSummary['late_count']               ?? 0);
            $evaluatedCount        = (int)   ($slaSummary['evaluated_count']          ?? ($onTimeCount + $lateCount));
            $compliancePercent     = (float) ($slaSummary['compliance_percent']       ?? 0);
            $worstDelayMinutes     = (int)   ($slaSummary['worst_delay_minutes']      ?? 0);
            $avgBreachDelayMinutes = (int)   ($slaSummary['avg_breach_delay_minutes'] ?? 0);
            $nearBreachCount       = (int)   ($slaSummary['near_breach_count']        ?? 0);
            $riskStatus            = (string)($slaSummary['risk_status']              ?? 'unknown');
            $riskLabel             = (string)($slaSummary['risk_label']               ?? 'Belum ada data');

            $distributionBase = max(1, $evaluatedCount);
            $onTimePercent    = $evaluatedCount > 0
                ? round(($onTimeCount / $distributionBase) * 100, 1)
                : 0;
            $latePercent      = $evaluatedCount > 0
                ? round(($lateCount / $distributionBase) * 100, 1)
                : 0;

            $riskStyles = [
                'safe'     => ['badge' => 'border-slate-200 bg-white text-slate-700', 'dot' => 'bg-emerald-500'],
                'warning'  => ['badge' => 'border-slate-200 bg-white text-slate-700', 'dot' => 'bg-amber-500'],
                'critical' => ['badge' => 'border-slate-200 bg-white text-slate-700', 'dot' => 'bg-rose-500'],
                'unknown'  => ['badge' => 'border-slate-200 bg-white text-slate-600', 'dot' => 'bg-slate-400'],
            ];
            $riskUi = $riskStyles[$riskStatus] ?? $riskStyles['unknown'];

            $resolvedSource = method_exists($resolved, 'getCollection')
                ? $resolved->getCollection()
                : $resolved;

            $slaTimeline = collect($resolvedSource)
                ->map(function ($item) use ($timezone) {
                    $sla = is_array($item->sla ?? null) ? $item->sla : null;
                    if (! $sla || ! $item->changed_at) return null;

                    $target = $sla['target_minutes']   ?? $sla['target_sla_minutes']          ?? null;
                    $actual = $sla['actual_minutes']   ?? $sla['actual_completion_minutes']    ?? null;

                    if (! is_numeric($target) || ! is_numeric($actual)) return null;

                    $target = (int) round((float) $target);
                    $actual = (int) round((float) $actual);

                    return [
                        'ticket'         => (string) ($item->bug->ticket ?? ('#' . $item->bug_id)),
                        'title'          => (string) ($item->bug?->title ?? ''),
                        'date_label'     => $item->changed_at->timezone($timezone)->locale('id')->translatedFormat('d M'),
                        'date_sort'      => $item->changed_at->timezone($timezone)->format('Y-m-d H:i:s'),
                        'target_minutes' => $target,
                        'actual_minutes' => $actual,
                        'delta_minutes'  => $target - $actual,
                        'status'         => $actual <= $target ? 'met' : 'breached',
                    ];
                })
                ->filter()
                ->sortBy('date_sort')
                ->values()
                ->all();
        @endphp

        <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                        SLA Snapshot
                    </p>
                    <p class="mt-1 text-sm font-medium text-slate-900">
                        {{ $selectedProgrammer->name }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $periodLabel }}
                    </p>
                </div>

                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium {{ $riskUi['badge'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $riskUi['dot'] }}"></span>
                    {{ $riskLabel }}
                </span>
            </div>

            {{-- Body --}}
            <div class="space-y-5 px-6 py-5">

                {{-- Secondary Metrics --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

                    {{-- 1. SLA Compliance --}}
                    <div class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">
                                    SLA Compliance
                                </p>
                                <p class="mt-3 text-3xl font-semibold tracking-tight tabular-nums text-slate-900">
                                    {{ number_format($compliancePercent, 1) }}%
                                </p>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    {{ $onTimeCount }} dari {{ $evaluatedCount }} tiket terukur
                                </p>
                            </div>
                            <div class="pm-card-icon-wrap flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Worst Delay --}}
                    <div class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">
                                    Worst Delay
                                </p>
                                <p class="mt-3 text-xl font-semibold leading-tight tracking-tight text-slate-900 sm:text-2xl">
                                    {{ $worstDelayMinutes > 0 ? $formatDurationMinutes($worstDelayMinutes) : '0 menit' }}
                                </p>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    {{ $worstDelayMinutes > 0 ? 'Rekor keterlambatan' : 'Tidak ada tiket telat' }}
                                </p>
                            </div>
                            <div class="pm-card-icon-wrap flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Avg Breach Delay --}}
                    <div class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">
                                    Avg Breach Delay
                                </p>
                                <p class="mt-3 text-xl font-semibold leading-tight tracking-tight text-slate-900 sm:text-2xl">
                                    {{ $avgBreachDelayMinutes > 0 ? $formatDurationMinutes($avgBreachDelayMinutes) : '0 menit' }}
                                </p>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    Rata-rata waktu molor
                                </p>
                            </div>
                            <div class="pm-card-icon-wrap flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Near Breach --}}
                    <div class="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-[0_8px_24px_-16px_rgba(15,23,42,0.18)]">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">
                                    Near Breach
                                </p>
                                <p class="mt-3 text-3xl font-semibold tracking-tight tabular-nums text-slate-900">
                                    {{ number_format($nearBreachCount) }}
                                </p>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    Tiket ≤ 2 jam sebelum limit
                                </p>
                            </div>
                            <div class="pm-card-icon-wrap flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v.01" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Distribution Summary --}}
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        On Time {{ number_format($onTimePercent, 1) }}%
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        Breach {{ number_format($latePercent, 1) }}%
                    </span>
                </div>

                {{-- SLA Delta Chart --}}
                <div
                    id="pm-sla-analytics"
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white"
                    data-sla-timeline='@json($slaTimeline, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
                >
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                                SLA Delta Chart
                            </p>
                            <p class="mt-1 text-sm font-medium text-slate-900">
                                Margin penyelesaian per tiket
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Positif = selesai sebelum limit, negatif = lewat limit.
                            </p>
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $evaluatedCount }} tiket dievaluasi
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 pt-6 sm:pt-6">
                        <div id="pm-sla-ranked-chart-canvas"></div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- ============================================================
         JavaScript
         ============================================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // ----------------------------------------------------------
            // Filter Submit Handler
            // ----------------------------------------------------------
            const filterForm      = document.getElementById('pm-kinerja-filter-form');
            const loadingSkeleton = document.getElementById('pm-kinerja-loading-skeleton');
            const mainContent     = document.getElementById('pm-kinerja-main-content');
            const submitButton    = filterForm?.querySelector('[data-filter-submit]');
            const submitLabel     = filterForm?.querySelector('[data-filter-submit-label]');

            let isSubmitting = false;

            if (filterForm && loadingSkeleton && mainContent && submitButton && submitLabel) {
                filterForm.addEventListener('submit', (event) => {
                    if (isSubmitting) { event.preventDefault(); return; }
                    event.preventDefault();
                    isSubmitting = true;
                    submitButton.setAttribute('disabled', 'disabled');
                    submitButton.classList.add('opacity-80', 'cursor-not-allowed');
                    submitLabel.textContent = 'Memuat...';
                    mainContent.classList.add('hidden');
                    loadingSkeleton.classList.remove('hidden');
                    window.requestAnimationFrame(() => {
                        window.requestAnimationFrame(() => filterForm.submit());
                    });
                });
            }

            // ----------------------------------------------------------
            // Helpers
            // ----------------------------------------------------------
            const PM_COLOR_PRIMARY = '#8a0b4e';
            const PM_COLOR_HOVER   = '#6d0940';
            const PM_COLOR_SOFT    = '#f5e8ef';

            const numberFormatter = new Intl.NumberFormat('id-ID');

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const truncate = (value, length = 72) => {
                const text = String(value ?? '');
                return text.length > length ? `${text.slice(0, length - 1)}…` : text;
            };

            const toNumber = (value) => {
                const n = Number(value ?? 0);
                return Number.isFinite(n) ? n : 0;
            };

            const formatDurationMinutes = (value) => {
                const total   = Math.max(0, Math.round(toNumber(value)));
                const days    = Math.floor(total / 1440);
                const hours   = Math.floor((total % 1440) / 60);
                const minutes = total % 60;
                const parts   = [];
                if (days > 0)                          parts.push(`${numberFormatter.format(days)} hari`);
                if (hours > 0)                         parts.push(`${numberFormatter.format(hours)} jam`);
                if (minutes > 0 || parts.length === 0) parts.push(`${numberFormatter.format(minutes)} menit`);
                return parts.join(' ');
            };

            const formatSignedDurationMinutes = (value) => {
                const minutes = Math.round(toNumber(value));
                if (minutes === 0) return '0 menit';
                return `${minutes > 0 ? '+' : '-'}${formatDurationMinutes(Math.abs(minutes))}`;
            };

            // ----------------------------------------------------------
            // Tooltip
            // ----------------------------------------------------------
            const bindModernTooltip = (canvasEl) => {
                const surface = canvasEl.querySelector('[data-chart-surface]');
                if (!surface) return;

                const targets = surface.querySelectorAll('[data-chart-tooltip]');
                if (!targets.length) return;

                surface.querySelectorAll('[data-chart-tooltip-el]').forEach((el) => el.remove());

                const tooltip = document.createElement('div');
                tooltip.className = 'pointer-events-none absolute z-30 hidden rounded-xl border border-slate-200/80 bg-white px-3.5 py-3 text-[11px] text-slate-600 shadow-sm';
                tooltip.style.maxWidth   = '220px';
                tooltip.style.minWidth   = '160px';
                tooltip.style.wordBreak  = 'break-word';
                tooltip.style.whiteSpace = 'normal';
                tooltip.setAttribute('data-chart-tooltip-el', 'true');
                surface.appendChild(tooltip);

                let activeTarget = null;

                const positionTooltip = (target) => {
                    const surfaceRect = surface.getBoundingClientRect();
                    const targetRect  = target.getBoundingClientRect();

                    tooltip.style.visibility = 'hidden';
                    tooltip.style.display    = 'block';
                    const ttW = tooltip.offsetWidth  || 200;
                    const ttH = tooltip.offsetHeight || 110;
                    tooltip.style.visibility = '';
                    tooltip.style.display    = '';

                    const GAP  = 10;
                    let left   = (targetRect.left + targetRect.width / 2) - surfaceRect.left - (ttW / 2);
                    left       = Math.max(GAP, Math.min(surfaceRect.width - ttW - GAP, left));
                    let top    = (targetRect.top - surfaceRect.top) - ttH - GAP;
                    if (top < GAP) top = (targetRect.bottom - surfaceRect.top) + GAP;
                    if (top + ttH > surfaceRect.height - GAP) top = surfaceRect.height - ttH - GAP;
                    top = Math.max(GAP, top);

                    tooltip.style.left      = `${left}px`;
                    tooltip.style.top       = `${top}px`;
                    tooltip.style.transform = 'none';
                };

                const setTargetState = (target, active) => {
                    const tag = (target.tagName || '').toLowerCase();
                    if (tag === 'path' || tag === 'rect' || tag === 'div') {
                        target.style.opacity = active ? '1' : (target.dataset.baseOpacity || '');
                        if (tag === 'path' || tag === 'rect') {
                            target.setAttribute('stroke',       active ? (target.dataset.activeStroke      || '#ffffff') : (target.dataset.baseStroke      || 'none'));
                            target.setAttribute('stroke-width', active ? (target.dataset.activeStrokeWidth || '1')       : (target.dataset.baseStrokeWidth || '0'));
                        }
                        return;
                    }
                    target.style.transform = active ? 'translateY(-2px)' : '';
                };

                const showTooltip = (target) => {
                    const title  = target.dataset.tooltipTitle  || '';
                    const meta   = target.dataset.tooltipMeta   || '';
                    const note   = target.dataset.tooltipNote   || '';
                    const value  = target.dataset.tooltipValue  || '';
                    const accent = target.dataset.tooltipAccent || '#0f172a';

                    tooltip.innerHTML = `
                        <div class="min-w-[160px]">
                            ${meta  ? `<p class="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-400">${escapeHtml(meta)}</p>` : ''}
                            ${title ? `<p class="mt-0.5 text-xs font-medium text-slate-900">${escapeHtml(title)}</p>` : ''}
                            ${note  ? `<p class="mt-1 text-[11px] leading-relaxed text-slate-500">${escapeHtml(note)}</p>` : ''}
                            ${value ? `
                                <div class="mt-1.5 flex items-start gap-2">
                                    <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full" style="background:${accent}"></span>
                                    <p class="text-[11px] leading-relaxed text-slate-600">${escapeHtml(value)}</p>
                                </div>
                            ` : ''}
                        </div>
                    `;

                    tooltip.classList.remove('hidden');
                    positionTooltip(target);

                    if (activeTarget && activeTarget !== target) {
                        setTargetState(activeTarget, false);
                    }
                    activeTarget = target;
                    setTargetState(target, true);
                };

                const hideTooltip = () => {
                    tooltip.classList.add('hidden');
                    if (activeTarget) {
                        setTargetState(activeTarget, false);
                        activeTarget = null;
                    }
                };

                targets.forEach((target) => {
                    target.addEventListener('mouseenter', () => showTooltip(target));
                    target.addEventListener('mousemove',  () => { if (!tooltip.classList.contains('hidden')) positionTooltip(target); });
                    target.addEventListener('mouseleave', hideTooltip);
                    target.addEventListener('focus',      () => showTooltip(target));
                    target.addEventListener('blur',       hideTooltip);
                });

                surface.addEventListener('mouseleave', hideTooltip);
            };

            // ----------------------------------------------------------
            // Performance Chart
            // ----------------------------------------------------------
            const initPerformanceChart = () => {
                const wrapper = document.getElementById('pm-performance-analytics');
                const canvas  = document.getElementById('pm-performance-chart-canvas');
                if (!wrapper || !canvas) return;

                let rows = [];
                try { rows = JSON.parse(wrapper.dataset.chart || '[]'); } catch { rows = []; }

                const modeButtons = wrapper.querySelectorAll('[data-chart-mode]');
                let activeMode    = 'summary';

                const normalizeRow = (row, index) => {
                    const slaMet      = Math.max(0, toNumber(row.sla_met      ?? row.slaMet      ?? row.on_time ?? 0));
                    const slaBreached = Math.max(0, toNumber(row.sla_breached ?? row.slaBreached ?? row.late    ?? 0));
                    const measured    = slaMet + slaBreached;
                    const rawTotal    = Math.max(0, toNumber(row.current ?? row.total ?? measured));
                    const total       = measured > 0 ? measured : rawTotal;
                    return {
                        index, id: toNumber(row.id),
                        label: String(row.label ?? 'Tanpa nama'),
                        total, slaMet, slaBreached,
                        worstDelayMinutes:  Math.max(0, toNumber(row.worst_delay_minutes  ?? row.max_delay_minutes  ?? 0)),
                        tightMarginMinutes: Math.max(0, toNumber(row.tight_margin_minutes ?? row.min_margin_minutes ?? 0)),
                    };
                };

                const allRows = Array.isArray(rows)
                    ? rows.map(normalizeRow).filter((r) => r.total > 0).sort((a, b) => b.total - a.total)
                    : [];

                const teamTotal      = allRows.reduce((s, r) => s + r.total,       0);
                const teamMet        = allRows.reduce((s, r) => s + r.slaMet,      0);
                const teamBreached   = allRows.reduce((s, r) => s + r.slaBreached, 0);
                const teamMeasured   = teamMet + teamBreached;
                const teamCompliance = teamMeasured > 0 ? (teamMet / teamMeasured) * 100 : 0;

                const transitionCanvas = (renderFn) => {
                    canvas.style.opacity   = '0';
                    canvas.style.transform = 'translateY(4px)';
                    setTimeout(() => {
                        renderFn();
                        void canvas.offsetHeight;
                        canvas.style.opacity   = '1';
                        canvas.style.transform = 'translateY(0)';
                    }, 120);
                };

                const renderEmpty = () => {
                    canvas.innerHTML = `
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                            <p class="text-sm font-medium text-slate-900">Tidak ada data di rentang ini</p>
                            <p class="mt-1 text-sm text-slate-500">Pilih rentang lain untuk melihat produktivitas programmer.</p>
                            <a href="#pm-kinerja-filter" class="mt-4 inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 transition-all duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#f5e8ef] hover:text-[#8a0b4e]">
                                Pilih rentang lain
                            </a>
                        </div>
                    `;
                };

                const renderSummary = () => {
                    if (!allRows.length) { renderEmpty(); return; }

                    const maxTotal = Math.max(1, ...allRows.map((r) => r.total));

                    const rowsHtml = allRows.map((row) => {
                        const measured   = row.slaMet + row.slaBreached;
                        const compliance = measured > 0 ? (row.slaMet / measured) * 100 : 0;
                        const base       = Math.max(1, measured);
                        const metPct     = (row.slaMet      / base) * 100;
                        const breachPct  = (row.slaBreached / base) * 100;
                        const totalW     = Math.max(10, (row.total / maxTotal) * 100);
                        const compClass  = measured > 0
                            ? (compliance >= 90 ? 'text-emerald-600' : compliance >= 75 ? 'text-amber-600' : 'text-rose-600')
                            : 'text-slate-400';

                        return `
                            <div class="grid grid-cols-[minmax(0,160px)_minmax(0,1fr)_56px] items-center gap-4 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-medium text-slate-700" title="${escapeHtml(row.label)}">${escapeHtml(row.label)}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-400">${numberFormatter.format(row.total)} bug</p>
                                </div>
                                <div class="relative">
                                    <div class="h-7 overflow-hidden rounded-lg bg-slate-100">
                                        <div class="flex h-full overflow-hidden rounded-lg transition-all duration-300" style="width:${totalW}%">
                                            ${row.slaMet > 0 ? `
                                                <div
                                                    class="flex h-full items-center justify-center bg-emerald-500 text-[10px] font-medium text-white"
                                                    style="width:${metPct}%"
                                                    data-chart-tooltip
                                                    data-tooltip-title="${escapeHtml(row.label)}"
                                                    data-tooltip-meta="SLA Met"
                                                    data-tooltip-value="${numberFormatter.format(row.slaMet)} bug selesai tepat waktu"
                                                    data-tooltip-accent="#10b981"
                                                    data-base-opacity="1"
                                                    tabindex="0"
                                                >${metPct >= 18 ? numberFormatter.format(row.slaMet) : ''}</div>
                                            ` : ''}
                                            ${row.slaBreached > 0 ? `
                                                <div
                                                    class="flex h-full items-center justify-center bg-rose-500 text-[10px] font-medium text-white"
                                                    style="width:${breachPct}%"
                                                    data-chart-tooltip
                                                    data-tooltip-title="${escapeHtml(row.label)}"
                                                    data-tooltip-meta="SLA Breached"
                                                    data-tooltip-value="${numberFormatter.format(row.slaBreached)} bug melewati target"
                                                    data-tooltip-accent="#f43f5e"
                                                    data-base-opacity="1"
                                                    tabindex="0"
                                                >${breachPct >= 18 ? numberFormatter.format(row.slaBreached) : ''}</div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <span class="text-xs font-medium ${compClass}">${measured > 0 ? `${compliance.toFixed(0)}%` : '–'}</span>
                                </div>
                            </div>
                        `;
                    }).join('');

                    canvas.innerHTML = `
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>SLA Met</span>
                                <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-rose-500"></span>SLA Breached</span>
                            </div>
                            <div data-chart-surface class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white">
                                <div class="divide-y divide-slate-100">${rowsHtml}</div>
                            </div>
                        </div>
                    `;

                    bindModernTooltip(canvas);
                };

                const renderDetail = () => {
                    if (!allRows.length) { renderEmpty(); return; }

                    const topTotal      = Math.max(0, ...allRows.map((r) => r.total));
                    const topPerformers = allRows.filter((r) => r.total === topTotal);

                    const formatNameList = (names) => {
                        if (!names.length) return '';
                        if (names.length === 1) return names[0];
                        if (names.length === 2) return `${names[0]} dan ${names[1]}`;
                        return `${names.slice(0, -1).join(', ')}, dan ${names[names.length - 1]}`;
                    };

                    const worstBreach = [...allRows].filter((r) => r.worstDelayMinutes > 0).sort((a, b) => b.worstDelayMinutes - a.worstDelayMinutes)[0] ?? null;
                    const thinMargin  = [...allRows].filter((r) => r.tightMarginMinutes > 0 && r.tightMarginMinutes < 10).sort((a, b) => a.tightMarginMinutes - b.tightMarginMinutes)[0] ?? null;
                    const topNames    = topPerformers.map((r) => r.label);

                    const items = [
                        { label: 'Produktivitas tertinggi', text: topPerformers.length > 0 ? `${formatNameList(topNames.slice(0, 3))}${topNames.length > 3 ? ` dan ${topNames.length - 3} lainnya` : ''} di posisi tertinggi dengan ${numberFormatter.format(topTotal)} bug.` : 'Belum ada data pada periode ini.' },
                        { label: 'Pelanggaran tertinggi',   text: worstBreach ? `${worstBreach.label} memiliki keterlambatan ${formatDurationMinutes(worstBreach.worstDelayMinutes)}.` : 'Tidak ada pelanggaran SLA pada periode ini.' },
                        { label: 'Margin paling tipis',     text: thinMargin  ? `${thinMargin.label} menyelesaikan tugas ${numberFormatter.format(thinMargin.tightMarginMinutes)} menit sebelum limit.` : 'Tidak ada penyelesaian di bawah 10 menit sebelum batas SLA.' },
                        { label: 'Kesimpulan tim',          text: teamMeasured > 0 ? `Tim menyelesaikan ${numberFormatter.format(teamTotal)} bug, ${numberFormatter.format(teamMeasured)} terukur, compliance ${teamCompliance.toFixed(1)}%.` : `Tim menyelesaikan ${numberFormatter.format(teamTotal)} bug, belum ada tiket terukur SLA.` },
                    ];

                    canvas.innerHTML = `
                        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white">
                            <div class="divide-y divide-slate-100">
                                ${items.map((item) => `
                                    <div class="px-5 py-4">
                                        <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">${escapeHtml(item.label)}</p>
                                        <p class="mt-2 text-sm leading-relaxed text-slate-700">${escapeHtml(item.text)}</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                };

                const render = () => {
                    if (!allRows.length) { renderEmpty(); return; }
                    activeMode === 'detail' ? renderDetail() : renderSummary();
                };

                const setActiveMode = (mode) => {
                    activeMode = mode === 'detail' ? 'detail' : 'summary';
                    modeButtons.forEach((btn) => {
                        const isActive = btn.dataset.chartMode === activeMode;
                        if (isActive) {
                            btn.style.backgroundColor = PM_COLOR_PRIMARY;
                            btn.style.color           = '#ffffff';
                            btn.classList.add('shadow-sm');
                            btn.classList.remove('hover:bg-[#f5e8ef]', 'hover:text-[#8a0b4e]');
                        } else {
                            btn.style.backgroundColor = '';
                            btn.style.color           = '';
                            btn.classList.remove('shadow-sm');
                            btn.classList.add('hover:bg-[#f5e8ef]', 'hover:text-[#8a0b4e]');
                        }
                    });
                    transitionCanvas(render);
                };

                modeButtons.forEach((btn) => {
                    btn.addEventListener('click', () => setActiveMode(btn.dataset.chartMode || 'summary'));
                });

                setActiveMode('summary');
            };

            // ----------------------------------------------------------
            // SLA Delta Chart
            // ----------------------------------------------------------
            const initSlaChart = () => {
                const wrapper = document.getElementById('pm-sla-analytics');
                const canvas  = document.getElementById('pm-sla-ranked-chart-canvas');
                if (!wrapper || !canvas) return;

                let rows = [];
                try { rows = JSON.parse(wrapper.dataset.slaTimeline || '[]'); } catch { rows = []; }

                if (!Array.isArray(rows) || rows.length === 0) {
                    canvas.innerHTML = `
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                            <p class="text-sm font-medium text-slate-900">Belum ada tiket terukur SLA</p>
                            <p class="mt-1 text-sm text-slate-500">Grafik akan tampil saat ada tiket dengan target SLA valid.</p>
                        </div>
                    `;
                    return;
                }

                const normalizedRows = rows.map((row, index) => {
                    const targetMinutes = toNumber(row.target_minutes);
                    const actualMinutes = toNumber(row.actual_minutes);
                    const deltaMinutes  = Number.isFinite(Number(row.delta_minutes))
                        ? Number(row.delta_minutes)
                        : (targetMinutes - actualMinutes);
                    return {
                        index,
                        ticket:    String(row.ticket    ?? `#${index + 1}`),
                        title:     String(row.title     ?? ''),
                        dateLabel: String(row.date_label ?? ''),
                        targetMinutes, actualMinutes, deltaMinutes,
                        status: String(row.status ?? (deltaMinutes >= 0 ? 'met' : 'breached')),
                    };
                });

                const rawMaxAbs      = Math.max(30, ...normalizedRows.map((r) => Math.abs(r.deltaMinutes)));
                const stepCandidates = [15, 30, 60, 120, 180, 240, 360, 480, 720, 1440];
                let tickStep         = stepCandidates[stepCandidates.length - 1];
                for (const c of stepCandidates) {
                    if (Math.ceil(rawMaxAbs / c) <= 6) { tickStep = c; break; }
                }
                const domainMax = Math.max(tickStep, Math.ceil(rawMaxAbs / tickStep) * tickStep);

                const count        = normalizedRows.length;
                const minSlotWidth = count <= 8 ? 64 : count <= 16 ? 48 : count <= 28 ? 36 : 28;
                const minChartW    = 58 + 22 + (count * minSlotWidth);
                const canvasW      = Math.floor(canvas.getBoundingClientRect().width || 680);
                const width        = Math.max(minChartW, canvasW);
                const height       = 300;
                const margin       = { top: 24, right: 22, bottom: 52, left: 58 };
                const innerW       = width  - margin.left - margin.right;
                const innerH       = height - margin.top  - margin.bottom;
                const edgePad      = count === 1 ? 0 : Math.max(12, Math.min(20, innerW * 0.025));
                const plotW        = count === 1 ? innerW : Math.max(1, innerW - edgePad * 2);

                const x = (i) => count === 1
                    ? margin.left + innerW / 2
                    : margin.left + edgePad + (plotW * i) / Math.max(1, count - 1);

                const inferredSlot = count === 1 ? innerW : plotW / Math.max(1, count - 1);
                const maxBarWidth  = count <= 6 ? 28 : count <= 12 ? 22 : count <= 20 ? 16 : count <= 30 ? 12 : 8;
                const barW         = Math.max(6, Math.min(maxBarWidth, inferredSlot * 0.45));

                const y         = (v) => margin.top + ((domainMax - v) / (domainMax * 2)) * innerH;
                const baselineY = y(0);

                const buildBarPath = ({ xPos, widthValue, heightValue, positive }) => {
                    if (heightValue <= 0) return '';
                    const r = Math.min(5, widthValue / 2, heightValue);
                    if (positive) {
                        const topY = baselineY - heightValue;
                        return `M ${xPos} ${baselineY} L ${xPos} ${topY + r} Q ${xPos} ${topY} ${xPos + r} ${topY} L ${xPos + widthValue - r} ${topY} Q ${xPos + widthValue} ${topY} ${xPos + widthValue} ${topY + r} L ${xPos + widthValue} ${baselineY} Z`;
                    }
                    const bottomY = baselineY + heightValue;
                    return `M ${xPos} ${baselineY} L ${xPos + widthValue} ${baselineY} L ${xPos + widthValue} ${bottomY - r} Q ${xPos + widthValue} ${bottomY} ${xPos + widthValue - r} ${bottomY} L ${xPos + r} ${bottomY} Q ${xPos} ${bottomY} ${xPos} ${bottomY - r} L ${xPos} ${baselineY} Z`;
                };

                const ticks = [];
                for (let v = domainMax; v >= -domainMax; v -= tickStep) {
                    const yPos   = y(v);
                    const isBase = v === 0;
                    ticks.push(`
                        <g>
                            <line x1="${margin.left}" y1="${yPos}" x2="${width - margin.right}" y2="${yPos}"
                                stroke="${isBase ? '#cbd5e1' : '#f1f5f9'}"
                                stroke-width="${isBase ? '0.75' : '0.5'}"
                                ${!isBase ? 'stroke-dasharray="2 3"' : ''} />
                            <text x="${margin.left - 10}" y="${yPos + 4}" text-anchor="end"
                                fill="${isBase ? '#94a3b8' : '#cbd5e1'}" font-size="10" font-weight="400"
                                font-family="system-ui,-apple-system,sans-serif"
                            >${isBase ? '0' : formatSignedDurationMinutes(v)}</text>
                        </g>
                    `);
                }

                const needRotate    = inferredSlot < 44;
                const labelFontSize = count > 24 ? 8 : count > 16 ? 9 : 10;
                const dateLabelY    = height - (needRotate ? 6 : 14);

                const bars = normalizedRows.map((row, i) => {
                    const isMet   = row.status === 'met' || row.deltaMinutes >= 0;
                    const centerX = x(i);
                    const rawH    = Math.abs(y(row.deltaMinutes) - baselineY);
                    const minH    = row.deltaMinutes === 0 ? 3 : (Math.abs(row.deltaMinutes) < 10 ? 6 : 0);
                    const barH    = Math.max(rawH, minH);
                    const barX    = centerX - barW / 2;
                    const barPath = buildBarPath({ xPos: barX, widthValue: barW, heightValue: barH, positive: isMet });
                    const fill    = isMet ? '#10b981' : '#f43f5e';
                    const tipValue = `Deviasi ${formatSignedDurationMinutes(row.deltaMinutes)} · Target ${formatDurationMinutes(row.targetMinutes)} · Aktual ${formatDurationMinutes(row.actualMinutes)}`;

                    const dateLabelEl = needRotate
                        ? `<text x="${centerX}" y="${dateLabelY}" text-anchor="end" fill="#94a3b8" font-size="${labelFontSize}" font-weight="400" font-family="system-ui,-apple-system,sans-serif" transform="rotate(-45 ${centerX} ${dateLabelY})">${escapeHtml(row.dateLabel)}</text>`
                        : `<text x="${centerX}" y="${dateLabelY}" text-anchor="middle" fill="#94a3b8" font-size="${labelFontSize}" font-weight="400" font-family="system-ui,-apple-system,sans-serif">${escapeHtml(row.dateLabel)}</text>`;

                    return `
                        <g>
                            <path d="${barPath}" fill="${fill}" fill-opacity="0.65"
                                data-chart-tooltip
                                data-tooltip-title="${escapeHtml(row.ticket)}"
                                data-tooltip-meta="${escapeHtml(row.dateLabel)}"
                                data-tooltip-note="${escapeHtml(truncate(row.title || '-'))}"
                                data-tooltip-value="${escapeHtml(tipValue)}"
                                data-tooltip-accent="${fill}"
                                data-base-stroke="none" data-base-stroke-width="0" data-base-opacity="0.65"
                                data-active-stroke="#ffffff" data-active-stroke-width="1"
                                tabindex="0" class="cursor-pointer transition-all duration-150"
                                aria-label="${escapeHtml(`${row.ticket}: ${tipValue}`)}"
                            />
                            ${dateLabelEl}
                        </g>
                    `;
                }).join('');

                const needScroll = width > canvasW;
                canvas.innerHTML = `
                    <div data-chart-surface class="relative ${needScroll ? 'overflow-x-auto' : ''}">
                        <svg
                            viewBox="0 0 ${width} ${height}"
                            ${needScroll ? `width="${width}" height="${height}"` : ''}
                            role="img" aria-label="SLA Delta Chart"
                            class="${needScroll ? 'block' : 'block h-auto w-full'}"
                        >
                            ${ticks.join('')}
                            ${bars}
                        </svg>
                    </div>
                `;

                bindModernTooltip(canvas);
            };

            // ----------------------------------------------------------
            // Init
            // ----------------------------------------------------------
            initPerformanceChart();
            initSlaChart();

        });
    </script>

    {{-- --------------------------------------------------------
         7. Riwayat Perbaikan
         -------------------------------------------------------- --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                    Riwayat Perbaikan
                </p>
                <p class="mt-1 text-sm font-medium text-slate-900">
                    {{ $titleName }}
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $fixedInRange }} bug diperbaiki pada periode {{ $periodLabel }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold text-white"
                    style="background-color: #8a0b4e;"
                    title="{{ $selectedProgrammer?->name ?: 'Semua Programmer' }}"
                >
                    @if ($selectedProgrammer)
                        {{ strtoupper(substr($selectedProgrammer->name, 0, 1)) }}
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                            <path d="M7.5 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm9 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.75 18.25A3.25 3.25 0 0 1 7 15h1a3.25 3.25 0 0 1 3.25 3.25.75.75 0 0 1-.75.75h-6a.75.75 0 0 1-.75-.75ZM12.75 18.25A3.25 3.25 0 0 1 16 15h1a3.25 3.25 0 0 1 3.25 3.25.75.75 0 0 1-.75.75h-6a.75.75 0 0 1-.75-.75Z" />
                        </svg>
                    @endif
                </div>

                <div class="hidden sm:block text-right">
                    <p class="text-xs font-medium text-slate-700">
                        {{ $selectedProgrammer?->name ?: 'Semua Programmer' }}
                    </p>
                    <p class="text-[11px] text-slate-400">
                        {{ $selectedProgrammer ? 'Per programmer' : 'Gabungan tim' }}
                    </p>
                </div>
                <div class="hidden sm:block text-[#8a0b4e]">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.8"
                        class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 6l6 6-6 6" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="space-y-3">
                @forelse ($resolved as $h)
                    @php
                        $sla = is_array($h->sla ?? null) ? $h->sla : null;

                        $rawTitle   = (string) ($h->bug?->title ?? '-');
                        $cleanTitle = preg_replace('/\s*-\s*SLA\s+(Tepat|Lewat|Terlambat)(\s*\([^)]*\))?/iu', '', $rawTitle);
                        $cleanTitle = trim($cleanTitle ?: $rawTitle);

                        $slaStatus = $sla['status'] ?? null;
                        $slaNote   = $sla['detail_note'] ?? null;

                        $targetMinutes = $sla['target_minutes'] ?? $sla['target_sla_minutes'] ?? null;
                        $actualMinutes = $sla['actual_minutes'] ?? $sla['actual_completion_minutes'] ?? null;

                        $targetDurationLabel = $formatDurationMinutes($targetMinutes);
                        $actualDurationLabel = $formatDurationMinutes($actualMinutes);

                        $slaTextClass = match ($slaStatus) {
                            'on_time' => 'text-emerald-600',
                            'late'    => 'text-rose-600',
                            default   => 'text-slate-500',
                        };
                    @endphp

                    <a
                        href="{{ route('pm.issues.show', $h->bug_id) }}?return={{ urlencode(url()->full()) }}"
                        class="group block rounded-2xl border border-slate-200/80 bg-white px-5 py-4 transition-colors duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#f5e8ef]/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#8a0b4e]/20"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                                        {{ $h->bug->ticket ?? ('#' . $h->bug_id) }}
                                    </span>

                                    @if ($h->bug?->project?->name)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] font-medium text-slate-600">
                                            {{ $h->bug->project->name }}
                                        </span>
                                    @endif

                                    @if (! $selectedProgrammer && $h->bug?->assignee?->name)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[10px] font-medium text-slate-600">
                                            {{ $h->bug->assignee->name }}
                                        </span>
                                    @endif
                                </div>

                                <h4 class="mt-2 truncate text-sm font-medium leading-snug text-slate-900 transition-colors group-hover:text-[#8a0b4e]" title="{{ $cleanTitle }}">
                                    {{ $cleanTitle }}
                                </h4>

                                <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                    <span>
                                        Selesai {{ $h->changed_at?->timezone($timezone)->locale('id')->translatedFormat('d M Y, H:i') }}
                                    </span>

                                    @if ($sla && ($slaNote || ($targetDurationLabel !== null && $actualDurationLabel !== null)))
                                        <span class="text-slate-300">•</span>

                                        @if ($slaNote)
                                            <span class="font-medium {{ $slaTextClass }}">{{ $slaNote }}</span>
                                        @endif

                                        @if ($targetDurationLabel !== null && $actualDurationLabel !== null)
                                            <span class="text-slate-400">
                                                (Target {{ $targetDurationLabel }} • Selesai {{ $actualDurationLabel }})
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="hidden shrink-0 text-slate-300 transition-colors group-hover:text-[#8a0b4e] sm:block">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/30 px-6 py-10 text-center">
                        <p class="text-sm font-medium text-slate-900">
                            Tidak ada data di rentang ini
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Data perbaikan tidak ditemukan untuk filter saat ini.
                        </p>
                        <a
                            href="#pm-kinerja-filter"
                            class="mt-4 inline-flex h-9 items-center justify-center rounded-xl border border-slate-100 bg-white px-4 text-xs font-medium text-slate-500 transition-all duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#f5e8ef] hover:text-[#8a0b4e]"
                        >
                            Pilih rentang lain
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                @if ($resolved->hasPages())
                    <x-pagination :paginator="$resolved" />
                @endif
            </div>
        </div>
    </div>
</div>

@endsection