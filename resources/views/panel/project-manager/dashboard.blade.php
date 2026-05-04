@extends('layouts.project-manager')

@section('title', 'Dashboard Project Manager')

@section('content')

{{-- ============================================================
     Page Header
     ============================================================ --}}
<div class="mb-10">
    <p class="mb-2 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-slate-400">
        Dashboard
    </p>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
        Pantau dan Tindaklanjuti Bug
    </h1>
    <p class="mt-1.5 max-w-lg text-sm leading-relaxed text-slate-400">
        Lihat bug yang butuh perhatian dan tugaskan ke programmer dengan cepat.
    </p>
</div>

{{-- ============================================================
     Metric Cards
     ============================================================ --}}
<div class="mb-10 grid grid-cols-2 gap-3 xl:grid-cols-4">

    @php
        $metrics = [
            [
                'label'       => 'Butuh Penugasan',
                'value'       => $needsAssignmentCount,
                'description' => 'Menunggu programmer',
                'variant'     => $assignmentVariant,
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />',
            ],
            [
                'label'       => 'SLA Terlampaui',
                'value'       => $overdueSlaCount,
                'description' => 'Melewati batas waktu',
                'variant'     => $slaVariant,
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            ],
            [
                'label'       => 'Bug Kritis',
                'value'       => $criticalOpenCount,
                'description' => 'Belum terselesaikan',
                'variant'     => $criticalVariant,
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
            ],
            [
                'label'       => 'Dalam Proses',
                'value'       => $activeCount,
                'description' => 'Sedang dikerjakan',
                'variant'     => 'neutral',
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
            ],
        ];
    @endphp

    @foreach ($metrics as $metric)
        <x-pm.metric-card
            :label="$metric['label']"
            :value="$metric['value']"
            :description="$metric['description']"
            :variant="$metric['variant']"
            :icon="$metric['icon']"
        />
    @endforeach

</div>

{{-- ============================================================
     Butuh Penugasan
     ============================================================ --}}
<section aria-labelledby="assignment-heading" class="mx-auto max-w-5xl">

    {{-- Section Header --}}
    <div class="mb-5">

        <p class="mb-2 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-slate-400">
            Penugasan
        </p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-2">
                <h2
                    id="assignment-heading"
                    class="text-base font-bold tracking-tight text-slate-900"
                >
                    Butuh Penugasan
                </h2>

                <x-pm.tooltip-help aria-label="Panduan penugasan">
                    <p class="font-semibold text-slate-900">Cara menugaskan bug</p>
                    <p class="mt-1.5 leading-relaxed text-slate-500">
                        Bug di bawah belum memiliki programmer.
                        Tetapkan <strong class="font-semibold text-slate-700">prioritas</strong> terlebih
                        dahulu sebelum melakukan penugasan.
                    </p>
                </x-pm.tooltip-help>
            </div>

            <a
                href="{{ route('pm.issues.index') }}"
                class="group inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-slate-400 transition-all duration-150 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-200 focus-visible:ring-offset-1"
            >
                <span>Lihat semua bug</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="h-3.5 w-3.5 transition-transform duration-150 group-hover:translate-x-0.5"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M7.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L10.94 10 7.22 6.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                </svg>
            </a>

        </div>

        <p class="mt-1.5 text-sm text-slate-400">
            Bug baru yang belum memiliki programmer.
        </p>

    </div>

    {{-- List --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm shadow-slate-900/[0.04]">

        @forelse ($newBugs as $bug)
            <div class="{{ !$loop->first ? 'border-t border-slate-100/80' : '' }}">
                <x-pm.bug-assignment-row :bug="$bug" :programmers="$programmers" />
            </div>
        @empty
            <div class="flex flex-col items-center px-6 py-16 text-center">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl border border-slate-100 bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5"
                         class="h-5 w-5 text-slate-300" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Semua bug sudah ditugaskan</p>
                <p class="mt-1 max-w-[220px] text-sm text-slate-400">
                    Tidak ada bug baru yang perlu ditindaklanjuti saat ini.
                </p>
                <a
                    href="{{ route('pm.issues.index') }}"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-400 transition-colors duration-150 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-200"
                >
                    Lihat semua bug
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="h-3.5 w-3.5" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L10.94 10 7.22 6.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        @endforelse

    </div>

</section>

@endsection