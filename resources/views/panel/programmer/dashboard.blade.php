@extends('layouts.programmer')

@section('title', 'Dashboard Programmer')

@section('content')

    {{-- ============================================================
         Page Header
         ============================================================ --}}
    <div class="mb-8">
        <p class="mb-1.5 font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]">
            Programmer
        </p>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Dashboard
        </h1>
        <p class="mt-1 text-sm leading-relaxed text-slate-500">
            Semua bug aktif kamu ada di satu halaman. Cek prioritas, pantau SLA, lalu lanjutkan pengerjaan.
        </p>
    </div>

    {{-- ============================================================
         Metric Cards (sama dengan PM dashboard)
         ============================================================ --}}
    <div class="mb-8 grid grid-cols-2 gap-3 xl:grid-cols-4">

        {{-- Belum Diterima --}}
        <div class="h-full rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-slate-400">
                        Belum Diterima
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                        {{ number_format($assignedCount) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Perlu mulai dikerjakan
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Dalam Pengerjaan --}}
        <div class="h-full rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-slate-400">
                        Dalam Pengerjaan
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                        {{ number_format($inProgressCount) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Sedang dikerjakan
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Menunggu QA --}}
        <div class="h-full rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-slate-400">
                        Menunggu QA
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                        {{ number_format($testingCount) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Sudah dikirim ke pengujian
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Bug Aktif --}}
        <div class="h-full rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-slate-400">
                        Bug Aktif
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                        {{ number_format($totalTasks) }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Selesai 7 hari: {{ $resolvedThisWeek }}
                    </p>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================================
         Bug List — Antrean Kerja
         ============================================================ --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

        {{-- Section Header --}}
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]">
                        Antrean Kerja
                    </p>
                    <x-pm.tooltip-help aria-label="Panduan antrean bug">
                        <p class="font-semibold text-slate-900">Cara pakai antrean</p>
                        <p class="mt-1 leading-relaxed text-slate-600">
                            Dashboard ini menampilkan bug dengan status:
                            <strong class="text-slate-800">Ditugaskan</strong>,
                            <strong class="text-slate-800">Dalam Pengerjaan</strong>,
                            <strong class="text-slate-800">Pengujian</strong>, dan
                            <strong class="text-slate-800">Dikembalikan QA</strong>.
                        </p>
                    </x-pm.tooltip-help>
                </div>
                <p class="mt-1 text-base font-bold tracking-tight text-slate-900">Bug Ditugaskan ke Saya</p>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar bug aktif milik kamu berdasarkan prioritas antrean kerja.
                </p>
            </div>

            <a
                href="{{ route('programmer.kinerja') }}"
                class="group inline-flex shrink-0 items-center gap-1.5 self-start rounded-md px-1 py-1 text-sm font-semibold tracking-[-0.01em] text-slate-700 transition-all duration-150 hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.18)] focus-visible:ring-offset-1 sm:mt-5"
            >
                <span>Riwayat Kinerja</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     class="h-4 w-4 text-slate-400 transition-all duration-150 group-hover:translate-x-0.5 group-hover:text-[#8a0b4e]"
                     aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M7.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L10.94 10 7.22 6.28a.75.75 0 0 1 0-1.06Z"
                          clip-rule="evenodd" />
                </svg>
            </a>
        </div>

        {{-- Bug Rows --}}
        <div class="space-y-3 px-6 py-5">
            @forelse ($tasks as $bug)
                @php
                    $detailUrl = route('programmer.bugs.show', $bug) . '?return=' . urlencode(request()->fullUrl());

                    $rawTitle = (string) $bug->title;
                    $bugTitle = $rawTitle;
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

                    $createdAt = $bug->created_at;
                    $dateDisplay = $createdAt
                        ? ($createdAt->diffInDays(now()) < 7
                            ? str_replace(' yang lalu', ' lalu', $createdAt->locale('id')->diffForHumans())
                            : $createdAt->translatedFormat('d M Y'))
                        : '—';

                    $ticket = $bug->ticket ?? sprintf('#BUG-%06d', $bug->id);
                @endphp

                <div class="group flex items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 transition-colors duration-200 hover:border-[#8a0b4e]/10 hover:bg-[#8a0b4e]/[0.01]">

                    {{-- Left: Info --}}
                    <div class="min-w-0 flex-1">

                        {{-- Row 1: Ticket + Priority + Status --}}
                        <div class="mb-2 flex flex-wrap items-center gap-1.5">
                            <span class="font-mono text-[10px] font-semibold tracking-[0.06em] text-slate-400">
                                {{ $ticket }}
                            </span>

                            @if ($bug->priority)
                                <x-priority-badge :priority="$bug->priority" />
                            @endif

                            <x-pm.status-badge :status="$bug->status" />
                        </div>

                        {{-- Row 2: Title + SLA suffix --}}
                        <p class="text-sm font-semibold leading-snug text-slate-900">
                            <span>{{ $bugTitle }}</span>
                            @if ($bugTitleSuffix)
                                <span class="ml-1.5 text-[11px] font-medium {{ $bugTitleSuffixClass }}">
                                    — {{ $bugTitleSuffix }}
                                </span>
                            @endif
                        </p>

                        {{-- Row 3: Meta --}}
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                            <span>{{ $bug->guest_name ?? 'Guest' }}</span>
                            <span class="text-slate-300" aria-hidden="true">·</span>
                            <span>{{ $bug->project?->name ?? '—' }}</span>
                            <span class="text-slate-300" aria-hidden="true">·</span>
                            <span>{{ $dateDisplay }}</span>
                            @if ($bug->due_at)
                                <span class="text-slate-300" aria-hidden="true">·</span>
                                <span>SLA: {{ $bug->due_at->format('d M Y, H:i') }}</span>
                            @endif
                        </div>

                    </div>

                    {{-- Right: Status label + Button (TIDAK DIUBAH) --}}
                    <div class="flex shrink-0 items-center gap-3 self-center">

                        @if ($bug->status === 'Assigned')
                            <p class="text-xs font-semibold text-rose-600">Perlu mulai dikerjakan</p>
                        @elseif ($bug->status === 'In Progress')
                            <p class="text-xs font-semibold text-amber-600">Sedang dikerjakan</p>
                        @elseif ($bug->status === 'Testing')
                            <p class="text-xs font-semibold text-blue-600">Menunggu QA</p>
                        @elseif ($bug->status === 'Rejected')
                            <p class="text-xs font-semibold text-amber-600">Dikembalikan QA</p>
                        @endif

                        <a  href="{{ $detailUrl }}"
                            class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-medium text-slate-700 transition-colors hover:border-[#8a0b4e]/20 hover:bg-[#8a0b4e]/[0.02] hover:text-[#8a0b4e]"
                        >
                            Detail
                        </a>
                    </div>

                </div>
            @empty
                <div class="flex flex-col items-center px-6 py-14 text-center">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.6"
                             class="h-5 w-5 text-slate-400" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-900">Belum ada bug yang ditugaskan</p>
                    <p class="mt-1 max-w-xs text-sm text-slate-500">
                        Bug akan muncul di sini saat PM menugaskan bug ke kamu.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($tasks->hasPages())
            <div class="border-t border-slate-100 px-6 pb-5 pt-4">
                <x-pagination :paginator="$tasks" />
            </div>
        @endif

    </section>
@endsection