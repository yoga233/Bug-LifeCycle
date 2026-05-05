@extends('layouts.qa')

@section('title', 'QA - Antrian Pengujian')

@section('content')

@php
    $metrics = [
        [
            'label'       => 'Menunggu Validasi',
            'value'       => $waitingCount,
            'description' => 'Tiket yang siap diperiksa oleh QA',
            'variant'     => $waitingCount > 0 ? 'action' : 'neutral',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6m-6 0a1 1 0 0 0-1 1v1h8V4a1 1 0 0 0-1-1m-6 0H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-3" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4" />
            ',
        ],
        [
            'label'       => 'Perlu Perhatian',
            'value'       => $attentionCount,
            'description' => 'Tiket berisiko tinggi di antrian QA',
            'variant'     => $attentionCount > 0 ? 'warning' : 'neutral',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            ',
        ],
        [
            'label'       => 'Tertahan di QA',
            'value'       => $stalledCount,
            'description' => 'Belum divalidasi lebih dari 2 hari',
            'variant'     => $stalledCount > 0 ? 'danger' : 'neutral',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            ',
        ],
        [
            'label'       => 'Dikembalikan QA',
            'value'       => $rejectedCount,
            'description' => 'Perlu perbaikan lanjutan dari programmer',
            'variant'     => $rejectedCount > 0 ? 'warning' : 'neutral',
            'icon'        => '
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            ',
        ],
    ];

    $bugMetaItems = function ($bug) {
        $reportedAt = $bug->created_at?->locale('id')->diffForHumans();
        $reportedAt = $reportedAt ? str_replace(' yang lalu', ' lalu', $reportedAt) : '—';

        return [
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />',
                'html' => e($bug->project?->name ?? '—'),
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
                'html' => 'Dilaporkan oleh <span class="font-medium text-slate-700">' . e($bug->guest_name ?? 'Tidak diketahui') . '</span>',
            ],
            [
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
                'html' => e($reportedAt),
            ],
        ];
    };
@endphp

{{-- ============================================================
     Header Halaman
     ============================================================ --}}
<div class="mb-10">
    <p class="mb-2 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-[#8a0b4e]/60">
        Antrian Pengujian
    </p>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-800">
        Validasi Tiket
    </h1>
    <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-500">
        Tiket yang sudah selesai dikerjakan programmer dan menunggu validasi QA.
        Buka detail tiket untuk menyetujui atau mengembalikan ke programmer.
    </p>
</div>

{{-- ============================================================
     Kartu Metrik
     ============================================================ --}}
<div class="mb-10 grid grid-cols-2 gap-3 xl:grid-cols-4">
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
     Daftar Tiket
     ============================================================ --}}
<section aria-labelledby="testing-heading" class="mx-auto max-w-5xl">

    <div class="mb-2.5">
        <p class="mb-1.5 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-[#8a0b4e]/60">
            Validasi QA
        </p>

        <div class="flex items-center gap-1.5">
            <h2
                id="testing-heading"
                class="text-base font-semibold tracking-tight text-slate-800"
            >
                Tiket dalam Pengujian
            </h2>

            <x-pm.tooltip-help aria-label="Panduan validasi tiket">
                <p class="font-semibold text-slate-800">Cara memvalidasi tiket</p>
                <p class="mt-1.5 leading-relaxed text-slate-500">
                    Buka detail tiket untuk memeriksa hasil perbaikan.
                    Setujui jika sudah benar, atau kembalikan ke programmer jika masih ada masalah.
                </p>
            </x-pm.tooltip-help>
        </div>

        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
            Tiket di bawah ini sudah dikerjakan programmer dan siap untuk divalidasi.
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/[0.04]">
        @forelse ($bugs as $bug)
            @if (!$loop->first)
                <div class="mx-5 border-t border-slate-200/70"></div>
            @endif

            @php
                $detailUrl = route('qa.bugs.show', $bug) . '?return=' . urlencode(url()->full());
                $ticket = $bug->ticket ?? sprintf('#BUG-%06d', $bug->id);
            @endphp

            <div class="group px-5 py-5 transition-colors duration-150 hover:bg-[rgba(138,11,78,0.022)]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Informasi Tiket --}}
                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="font-mono text-[10px] font-medium tracking-[0.06em] text-slate-400">
                                {{ $ticket }}
                            </span>

                            @if ($bug->priority)
                                <x-priority-badge :priority="$bug->priority" />
                            @endif

                            @if ($bug->severity)
                                <x-severity-badge :severity="$bug->severity" />
                            @endif

                            <x-pm.status-badge :status="$bug->status" />
                        </div>

                        <h4 class="mt-2.5 text-sm font-medium leading-snug text-slate-800">
                            <a
                                href="{{ $detailUrl }}"
                                class="transition-colors duration-150 hover:text-[#8a0b4e]"
                            >
                                {{ $bug->title }}
                            </a>
                        </h4>

                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            @foreach ($bugMetaItems($bug) as $meta)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="1.75"
                                         class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                        {!! $meta['icon'] !!}
                                    </svg>
                                    <span>{!! $meta['html'] !!}</span>
                                </span>
                            @endforeach
                        </div>

                    </div>

                    {{-- Aksi --}}
                    <div class="flex shrink-0 items-center gap-2">
                        <a
                            href="{{ $detailUrl }}"
                            class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                        >
                            Lihat Detail
                        </a>
                    </div>

                </div>
            </div>

        @empty
            <div class="flex flex-col items-center px-6 py-20 text-center">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.75"
                         class="h-5 w-5 text-slate-400" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <p class="text-sm font-semibold text-slate-700">
                    Tidak ada tiket yang menunggu validasi
                </p>

                <p class="mt-1.5 max-w-xs text-sm leading-relaxed text-slate-500">
                    Semua tiket sudah divalidasi atau belum ada yang masuk tahap pengujian saat ini.
                </p>
            </div>
        @endforelse
    </div>

</section>

@endsection