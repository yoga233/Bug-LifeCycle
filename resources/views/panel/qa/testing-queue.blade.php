{{-- resources/views/qa/antrian.blade.php --}}
@extends('layouts.qa')

@section('title', 'QA - Antrian Pengujian')

@section('content')

<style>
    .qa-issue-title:visited { color: inherit; }
</style>

@php
    $metrics = [
        [
            'label'       => 'Total Bug',
            'value'       => $totalBugs,
            'description' => 'Seluruh laporan lintas status',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c-2 0-4-1-4-3s2-3 4-3 4 1 4 3-2 3-4 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.343 17.657A8 8 0 1 1 17.657 6.343 8 8 0 0 1 6.343 17.657Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9 7 7m6 0 2-2M9 15l-2 2m8-2 2 2" />
            ',
        ],
        [
            'label'       => 'Bug Aktif',
            'value'       => $activeCount,
            'description' => 'Dilaporkan · Ditugaskan · Progress · Testing',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z" />
            ',
        ],
        [
            'label'       => 'Dalam Pengujian',
            'value'       => $testingCount,
            'description' => 'Backlog validasi QA saat ini',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6m-6 0a1 1 0 0 0-1 1v1h8V4a1 1 0 0 0-1-1m-6 0H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-3" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4" />
            ',
        ],
        [
            'label'       => 'Selesai / Ditutup',
            'value'       => $resolvedCount,
            'description' => 'Bug yang sudah clear dari workflow',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            ',
        ],
    ];

    $bugMetaItems = fn ($bug) => [
        [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6" />',
            'text' => $bug->project?->name ?? '—',
        ],
        [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'text' => $bug->guest_name ?? 'Guest',
        ],
        [
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
            'text' => $bug->created_at?->format('d M Y'),
        ],
    ];
@endphp

{{-- ── Header ───────────────────────────────────────────────────────────── --}}
<div class="mb-8">

    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-xs">
        <span class="text-slate-400">QA</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
            class="h-3 w-3 text-slate-300" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                clip-rule="evenodd" />
        </svg>
        <span class="font-medium text-slate-600">Antrian Pengujian</span>
    </div>

    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Antrian Pengujian</h1>
    <p class="mt-1.5 text-sm text-slate-500">
        Daftar bug berstatus Pengujian yang menunggu validasi QA.
    </p>

</div>

{{-- ── Metric Cards ─────────────────────────────────────────────────────── --}}
<div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($metrics as $metric)
        <x-pm.metric-card
            :label="$metric['label']"
            :value="$metric['value']"
            :description="$metric['description']"
            :icon="$metric['icon']"
        />
    @endforeach
</div>

{{-- ── Bug List Section ─────────────────────────────────────────────────── --}}
<section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

    {{-- Section Header --}}
    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                Validasi QA
            </p>
            <p class="mt-1 text-sm font-medium text-slate-900">Bug Dalam Pengujian</p>
            <p class="mt-1 text-sm text-slate-500">
                Buka detail bug untuk approve atau kembalikan ke programmer.
            </p>
        </div>
    </div>

    {{-- Bug List --}}
    <div class="px-6 py-5">
        <div class="space-y-3">
            @forelse ($bugs as $bug)
                @php
                    $detailUrl = route('qa.bugs.show', $bug) . '?return=' . urlencode(url()->full());
                @endphp

                <div class="group flex items-start justify-between rounded-2xl border border-slate-100 bg-slate-50/30 px-6 py-4 transition-all duration-200 hover:border-slate-200 hover:bg-white">
                    <div class="min-w-0 flex-1">

                        {{-- Badges --}}
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs text-slate-500">
                                {{ $bug->ticket ?? ('#' . $bug->id) }}
                            </span>
                            @if ($bug->priority)
                                <x-priority-badge :priority="$bug->priority" />
                            @endif
                            @if ($bug->severity)
                                <x-severity-badge :severity="$bug->severity" />
                            @endif
                            <x-pm.status-badge :status="$bug->status" />
                        </div>

                        {{-- Title --}}
                        <a href="{{ $detailUrl }}"
                            class="mb-1 inline-block text-sm font-semibold text-slate-900 transition-colors hover:text-[#8a0b4e]">
                            {{ $bug->title }}
                        </a>

                        {{-- Description --}}
                        <p class="mb-3 line-clamp-1 text-xs text-slate-500">
                            {{ str((string) $bug->description)->limit(120) }}
                        </p>

                        {{-- Meta --}}
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
                            @foreach ($bugMetaItems($bug) as $meta)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        {!! $meta['icon'] !!}
                                    </svg>
                                    <span class="truncate">{{ $meta['text'] }}</span>
                                </span>
                            @endforeach
                        </div>

                    </div>

                    {{-- Action --}}
                    <div class="ml-4 shrink-0 self-center">
                        <a href="{{ $detailUrl }}"
                            class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-medium text-slate-700 transition-colors hover:border-[#8a0b4e]/20 hover:bg-[#8a0b4e]/[0.02] hover:text-[#8a0b4e]"
                        >
                            Detail
                        </a>
                    </div>
                </div>

            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/30 px-6 py-10 text-center">
                    <p class="text-sm font-medium text-slate-900">Antrian pengujian kosong</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Belum ada bug yang masuk tahap Pengujian saat ini.
                    </p>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$bugs" />
    </div>

</section>

@endsection