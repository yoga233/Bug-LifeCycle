@extends('layouts.programmer')

@section('title', 'Dashboard Programmer')

@section('content')

@php
    $metrics = [
        [
            'label'       => 'Belum Dikerjakan',
            'value'       => $assignedCount,
            'description' => 'Tiket yang sudah ditugaskan dan perlu dimulai',
            'variant'     => $assignedCount > 0 ? 'action' : 'neutral',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />',
        ],
        [
            'label'       => 'Dalam Pengerjaan',
            'value'       => $inProgressCount,
            'description' => 'Tiket yang sedang kamu kerjakan',
            'variant'     => $inProgressCount > 0 ? 'warning' : 'neutral',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />',
        ],
        [
            'label'       => 'Menunggu QA',
            'value'       => $testingCount,
            'description' => 'Sudah dikirim ke pengujian, menunggu validasi',
            'variant'     => 'neutral',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
        ],
        [
            'label'       => 'Total Tiket Aktif',
            'value'       => $totalTasks,
            'description' => 'Selesai 7 hari terakhir: ' . $resolvedThisWeek . ' tiket',
            'variant'     => 'neutral',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
        ],
    ];

    $statusLabel = fn ($status) => match ($status) {
        'Assigned'    => ['text' => 'Belum dimulai, segera kerjakan', 'class' => 'text-rose-600'],
        'In Progress' => ['text' => 'Sedang dalam pengerjaan',        'class' => 'text-amber-600'],
        'Testing'     => ['text' => 'Menunggu validasi QA',           'class' => 'text-blue-600'],
        'Rejected'    => ['text' => 'Ditolak QA, perlu diperbaiki',   'class' => 'text-rose-600'],
        default       => ['text' => $status,                           'class' => 'text-slate-500'],
    };
@endphp

{{-- ============================================================
     Header Halaman
     ============================================================ --}}
<div class="mb-10">
    <p class="mb-2 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-[#8a0b4e]/60">
        Programmer
    </p>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-800">
        Dashboard
    </h1>
    <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-500">
        Semua tiket aktif yang ditugaskan ke kamu ada di satu halaman.
        Cek prioritas, pantau SLA, lalu lanjutkan pengerjaan.
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
     Antrean Kerja
     ============================================================ --}}
<section aria-labelledby="queue-heading" class="mx-auto max-w-5xl">

    <div class="mb-2.5">
        <p class="mb-1.5 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-[#8a0b4e]/60">
            Antrean Kerja
        </p>

        <div class="flex items-center gap-1.5">
            <h2
                id="queue-heading"
                class="text-base font-semibold tracking-tight text-slate-800"
            >
                Tiket Ditugaskan ke Saya
            </h2>

            <x-pm.tooltip-help aria-label="Panduan antrean kerja">
                <p class="font-semibold text-slate-800">Tentang antrean kerja</p>
                <p class="mt-1.5 leading-relaxed text-slate-500">
                    Halaman ini menampilkan tiket dengan status
                    <strong class="font-semibold text-slate-700">Ditugaskan</strong>,
                    <strong class="font-semibold text-slate-700">Dalam Pengerjaan</strong>,
                    <strong class="font-semibold text-slate-700">Pengujian</strong>, dan
                    <strong class="font-semibold text-slate-700">Dikembalikan QA</strong>.
                </p>
            </x-pm.tooltip-help>
        </div>

        <div class="mt-1 flex items-end justify-between gap-4">
            <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                Daftar tiket aktif milik kamu berdasarkan prioritas antrean kerja.
            </p>

            <a
                href="{{ route('programmer.kinerja') }}"
                class="group inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-[#8a0b4e]/60 transition-all duration-150 hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
            >
                <span>Riwayat Kinerja</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    class="h-3.5 w-3.5 transition-transform duration-150 group-hover:translate-x-0.5"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    {{-- Daftar Tiket --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/[0.04]">
        @forelse ($tasks as $bug)
            @if (!$loop->first)
                <div class="mx-5 border-t border-slate-200/70"></div>
            @endif

            @php
                $detailUrl = route('programmer.bugs.show', $bug) . '?return=' . urlencode(request()->fullUrl());
                $ticket    = $bug->ticket ?? sprintf('BUG-%06d', $bug->id);

                $rawTitle       = (string) $bug->title;
                $bugTitle       = $rawTitle;
                $bugTitleSuffix = '';
                $bugTitleSuffixClass = 'text-slate-400';

                if (preg_match('/\s*-\s*(SLA\s+(?:Terlambat|Tepat|Terlewat)[^-]*?)(?:\s*-\s*|$)/iu', $rawTitle, $m)) {
                    $bugTitleSuffix = trim(preg_replace('/^SLA\s+/iu', '', (string) $m[1]));
                    $bugTitle = trim(str_replace($m[0], ' - ', $rawTitle), ' -');
                    $bugTitle = trim(preg_replace('/\s*-\s*-\s*/', ' - ', $bugTitle), ' -');

                    $sl = mb_strtolower($bugTitleSuffix);
                    if (str_contains($sl, 'terlambat') || str_contains($sl, 'terlewat')) {
                        $bugTitleSuffixClass = 'text-amber-600';
                    } elseif (str_contains($sl, 'tepat')) {
                        $bugTitleSuffixClass = 'text-emerald-600';
                    }
                }

                $createdAt   = $bug->created_at;
                $dateDisplay = $createdAt
                    ? ($createdAt->diffInDays(now()) < 7
                        ? str_replace(' yang lalu', ' lalu', $createdAt->locale('id')->diffForHumans())
                        : $createdAt->translatedFormat('d M Y'))
                    : '—';

                $isRevision = ($bug->status === 'In Progress' && ($bug->rejection_comments_count ?? 0) > 0);
                
                $sl = $statusLabel($bug->status);
                if ($isRevision) {
                    $sl = ['text' => 'Revisi (Perlu Perbaikan)', 'class' => 'text-rose-600'];
                }
            @endphp

            <div class="group px-5 py-5 transition-colors duration-150 hover:bg-[rgba(138,11,78,0.022)]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Informasi Tiket --}}
                    <div class="min-w-0 flex-1">

                        {{-- Baris 1: Ticket + Priority + Status --}}
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="font-mono text-[10px] font-medium tracking-[0.06em] text-slate-400">
                                {{ $ticket }}
                            </span>

                            @if ($bug->priority)
                                <x-priority-badge :priority="$bug->priority" />
                            @endif

                            <x-pm.status-badge :status="$isRevision ? 'Rejected' : $bug->status">
                                {{ $isRevision ? 'Revisi' : null }}
                            </x-pm.status-badge>
                        </div>

                        {{-- Baris 2: Judul + SLA suffix --}}
                        <h4 class="mt-2.5 text-sm font-medium leading-snug text-slate-800">
                            <a
                                href="{{ $detailUrl }}"
                                class="transition-colors duration-150 hover:text-[#8a0b4e]"
                            >
                                {{ $bugTitle }}
                            </a>
                            @if ($bugTitleSuffix)
                                <span class="ml-1.5 text-[11px] font-medium {{ $bugTitleSuffixClass }}">
                                    — {{ $bugTitleSuffix }}
                                </span>
                            @endif
                        </h4>

                        {{-- Baris 3: Meta --}}
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.75"
                                     class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                </svg>
                                <span>{{ $bug->project?->name ?? '—' }}</span>
                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.75"
                                     class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span>
                                    Dilaporkan oleh
                                    <span class="font-medium text-slate-700">{{ $bug->guest_name ?? 'Tidak diketahui' }}</span>
                                </span>
                            </span>

                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.75"
                                     class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span>{{ $dateDisplay }}</span>
                            </span>

                            @if ($bug->due_at)
                                @php
                                    $isOverdue = $bug->due_at->isPast() && !in_array($bug->status, ['Resolved', 'Closed']);
                                    $isNearDue = !$isOverdue && now()->diffInHours($bug->due_at, false) <= 6;

                                    $slaTimeClass = $isOverdue
                                        ? 'text-rose-600'
                                        : ($isNearDue ? 'text-amber-600' : 'text-slate-500');
                                @endphp

                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.75"
                                        class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span class="text-slate-400">SLA</span>
                                    <span class="font-medium {{ $slaTimeClass }}">
                                        {{ $bug->due_at->format('d M, H:i') }}
                                    </span>
                                </span>
                            @endif
                        </div>

                    </div>

                    {{-- Aksi --}}
                    <div class="flex shrink-0 items-center gap-3">
                        <p class="hidden text-xs font-semibold sm:block {{ $sl['class'] }}">
                            {{ $sl['text'] }}
                        </p>

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
                    Belum ada tiket yang ditugaskan
                </p>

                <p class="mt-1.5 max-w-xs text-sm leading-relaxed text-slate-500">
                    Tiket akan muncul di sini saat Project Manager menugaskan tiket ke kamu.
                </p>
            </div>
        @endforelse
    </div>

</section>

@endsection