{{-- resources/views/panel/project-manager/issues/index.blade.php --}}
@extends('layouts.project-manager')

@section('title', 'Manajemen Bug')

@section('content')

    {{-- ============================================================
         Page Header
         ============================================================ --}}
    <div class="mb-8">

        {{-- Breadcrumb --}}
        @php
            $returnUrl   = request('return');
            $returnLabel = 'Dashboard';

            if ($returnUrl) {
                if (str_contains($returnUrl, 'kinerja')) {
                    $returnLabel = 'Riwayat Kinerja';
                } elseif (str_contains($returnUrl, 'management')) {
                    $returnLabel = 'Manajemen';
                }
            }
        @endphp

        <nav class="mb-4 flex items-center gap-1.5" aria-label="Breadcrumb">
            <a
                href="{{ $returnUrl ?: route('pm.dashboard') }}"
                class="text-xs text-slate-400 transition-colors duration-150 hover:text-[#8a0b4e]"
            >
                {{ $returnLabel }}
            </a>

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                 class="h-3 w-3 shrink-0 text-slate-300" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                      clip-rule="evenodd" />
            </svg>

            <span class="text-xs font-medium text-slate-600" aria-current="page">Semua Bug</span>
        </nav>

        {{-- Title block --}}
        <p class="mb-1.5 font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]">
            Manajemen Bug
        </p>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Semua Bug
        </h1>
        <p class="mt-1 text-sm leading-relaxed text-slate-500">
            Pantau, filter, dan tindaklanjuti bug dari semua project.
        </p>
    </div>

    {{-- ============================================================
         Filter Section
         ============================================================ --}}
    <div class="mb-8 rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <form id="pm-issues-filter-form" method="GET" action="{{ route('pm.issues.index') }}" class="p-5">

            <p class="mb-4 font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]">
                Filter
            </p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

                {{-- Project --}}
                <div>
                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-[0.10em] text-slate-400">
                        Project
                    </label>
                    <x-pm.filter-dropdown
                        name="project_id"
                        :items="$projects->map(fn($p) => ['value' => (string) $p->id, 'label' => $p->name])->values()->all()"
                        :selected="(string) ($filters['project_id'] ?? '')"
                        placeholder="Semua Project"
                        :searchable="true"
                    />
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-[0.10em] text-slate-400">
                        Status
                    </label>
                    <x-pm.filter-dropdown
                        name="status"
                        :items="collect($statuses)->map(function ($s) {
                            $s = (string) $s;
                            $label = match ($s) {
                                'Reported'    => 'Dilaporkan',
                                'Assigned'    => 'Ditugaskan',
                                'In Progress' => 'Dalam Pengerjaan',
                                'Testing'     => 'Pengujian',
                                'Resolved'    => 'Diselesaikan',
                                'Closed'      => 'Ditutup',
                                'Rejected'    => 'Dikembalikan QA',
                                default       => $s,
                            };
                            return ['value' => $s, 'label' => $label];
                        })->values()->all()"
                        :selected="(string) ($filters['status'] ?? '')"
                        placeholder="Semua Status"
                        :searchable="false"
                    />
                </div>

                {{-- Priority --}}
                <div>
                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-[0.10em] text-slate-400">
                        Prioritas
                    </label>
                    <x-pm.filter-dropdown
                        name="priority_id"
                        :items="$priorities->map(fn($p) => ['value' => (string) $p->id, 'label' => $p->level . ' (' . $p->sla_hours . 'h)'])->values()->all()"
                        :selected="(string) ($filters['priority_id'] ?? '')"
                        placeholder="Semua Prioritas"
                        :searchable="false"
                    />
                </div>

                {{-- Assignee --}}
                <div>
                    <label class="mb-1 block text-[10px] font-medium uppercase tracking-[0.10em] text-slate-400">
                        Programmer
                    </label>
                    <x-pm.filter-dropdown
                        name="assignee_id"
                        :items="collect([['value' => 'unassigned', 'label' => 'Belum Ditugaskan']])->merge(
                            $assignees->map(fn($a) => ['value' => (string) $a->id, 'label' => $a->name])
                        )->values()->all()"
                        :selected="(string) ($filters['assignee_id'] ?? '')"
                        placeholder="Semua Programmer"
                        :searchable="true"
                    />
                </div>

                {{-- Search --}}
                <div>
                    <label for="pm-issues-search"
                           class="mb-1 block text-[10px] font-medium uppercase tracking-[0.10em] text-slate-400">
                        Cari
                    </label>
                    <input
                        id="pm-issues-search"
                        name="q"
                        type="text"
                        value="{{ $filters['q'] ?? '' }}"
                        placeholder="Judul atau #BUG-3FTNOD"
                        class="h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-800 placeholder:text-slate-400 transition-all duration-150 focus:border-[rgba(138,11,78,0.35)] focus:outline-none focus:ring-2 focus:ring-[rgba(138,11,78,0.10)]"
                    />
                </div>

            </div>

            {{-- Filter actions --}}
            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <p class="text-[10px] text-slate-400">
                    Kosongkan semua filter untuk melihat seluruh bug.
                </p>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('pm.issues.index') }}"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:bg-[rgba(138,11,78,0.01)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
                    >
                        Reset
                    </a>
                    <button
                        type="submit"
                        data-filter-submit
                        class="inline-flex h-8 items-center justify-center rounded-lg bg-[#8a0b4e] px-3.5 text-xs font-semibold text-white transition-colors duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span data-filter-submit-label>Terapkan</span>
                    </button>
                </div>

            </div>

        </form>
    </div>

    {{-- ============================================================
         Loading Skeleton
         ============================================================ --}}
    <div
        id="pm-issues-loading-skeleton"
        class="mb-8 hidden"
        aria-live="polite"
        aria-busy="true"
    >
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="animate-pulse">

                {{-- Skeleton: Section Header --}}
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <div class="h-2 w-10 rounded-full bg-slate-100"></div>
                        <div class="h-3.5 w-28 rounded-lg bg-slate-100"></div>
                    </div>
                    <div class="h-2.5 w-24 rounded-full bg-slate-100/80"></div>
                </div>

                {{-- Skeleton: Bug Rows --}}
                <div class="divide-y divide-slate-100">
                    @for ($i = 0; $i < 5; $i++)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">

                                <div class="min-w-0 flex-1">

                                    {{-- Row 1: Badge meta --}}
                                    <div class="mb-2 flex items-center gap-2">
                                        <div class="h-2.5 w-20 rounded-full bg-slate-100"></div>
                                        <div class="h-4 w-12 rounded-full bg-slate-100"></div>
                                        <div class="h-4 w-16 rounded-full bg-slate-100"></div>
                                    </div>

                                    {{-- Row 2: Title --}}
                                    <div class="h-3 w-3/5 rounded-lg bg-slate-100"></div>

                                    {{-- Row 3: Sub-meta --}}
                                    <div class="mt-2.5 flex items-center gap-4">
                                        <div class="h-2 w-20 rounded-full bg-slate-100/70"></div>
                                        <div class="h-2 w-24 rounded-full bg-slate-100/70"></div>
                                        <div class="h-2 w-16 rounded-full bg-slate-100/70"></div>
                                        <div class="h-2 w-20 rounded-full bg-slate-100/70"></div>
                                    </div>

                                </div>

                                <div class="hidden h-4 w-4 shrink-0 rounded bg-slate-100 sm:block"></div>

                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Skeleton: Pagination --}}
                <div class="border-t border-slate-100 px-6 pb-5 pt-4">
                    <div class="flex items-center justify-between">
                        <div class="h-2 w-28 rounded-full bg-slate-100/70"></div>
                        <div class="flex gap-1.5">
                            @for ($i = 0; $i < 5; $i++)
                                <div class="h-8 w-8 rounded-lg bg-slate-100"></div>
                            @endfor
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ============================================================
         Bug List
         ============================================================ --}}
    <div id="pm-issues-main-content" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

        {{-- Section Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <p class="mb-1 font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]">
                    Daftar
                </p>
                <h2 class="text-base font-bold tracking-tight text-slate-900">
                    Semua Bug
                </h2>
            </div>

            <p class="text-xs text-slate-500">
                <span class="font-semibold text-slate-700">{{ number_format($bugs->total()) }}</span>
                {{ $bugs->total() === 1 ? 'bug' : 'bug' }} ditemukan
            </p>
        </div>

        {{-- Bug Rows --}}
        <div class="divide-y divide-slate-100">
            @forelse ($bugs as $bug)
                @php
                    $assigneeName = $bug->assignee?->name;
                    $isUnassigned = is_null($assigneeName);

                    $createdAt = $bug->created_at;
                    $dateDisplay = $createdAt
                        ? ($createdAt->diffInDays(now()) < 7
                            ? str_replace(' yang lalu', ' lalu', $createdAt->locale('id')->diffForHumans())
                            : $createdAt->translatedFormat('d M Y'))
                        : '—';

                    $ticket = '#' . ($bug->ticket ?? sprintf('BUG-%06d', $bug->id));
                @endphp

                <a
                    href="{{ route('pm.issues.show', $bug) }}?return={{ urlencode(url()->full()) }}"
                    class="group block px-5 py-4 transition-colors duration-150 hover:bg-[#8a0b4e]/[0.015] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-inset"
                >
                    <div class="flex items-center justify-between gap-4">

                        {{-- Left: Info --}}
                        <div class="min-w-0 flex-1">

                            {{-- Row 1: Ticket ID + Priority + Status --}}
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="font-mono text-[10px] font-semibold tracking-[0.06em] text-slate-400">
                                    {{ $ticket }}
                                </span>

                                @if ($bug->priority)
                                    <x-priority-badge :priority="$bug->priority" />
                                @endif

                                <x-pm.status-badge :status="$bug->status" />
                            </div>

                            {{-- Row 2: Title --}}
                            <h3 class="mt-1.5 truncate text-sm font-semibold text-slate-900 transition-colors duration-150 group-hover:text-[#8a0b4e]">
                                {{ $bug->title }}
                            </h3>

                            {{-- Row 3: Meta --}}
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">

                                {{-- Reporter --}}
                                <span class="inline-flex items-center gap-1 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                        <path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-5.5 6.25A4.75 4.75 0 0 1 9.25 11.5h1.5a4.75 4.75 0 0 1 4.75 4.75.75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" />
                                    </svg>
                                    {{ $bug->guest_name }}
                                </span>

                                <span class="text-slate-300" aria-hidden="true">·</span>

                                {{-- Project --}}
                                <span class="inline-flex items-center gap-1 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                              d="M4.25 2A2.25 2.25 0 0 0 2 4.25v11.5A2.25 2.25 0 0 0 4.25 18h11.5A2.25 2.25 0 0 0 18 15.75V4.25A2.25 2.25 0 0 0 15.75 2H4.25Zm4.03 6.28a.75.75 0 0 0-1.06-1.06L4.97 9.47a.75.75 0 0 0 0 1.06l2.25 2.25a.75.75 0 0 0 1.06-1.06L6.56 10l1.72-1.72Zm4.5-1.06a.75.75 0 1 0-1.06 1.06L13.44 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06l2.25-2.25a.75.75 0 0 0 0-1.06l-2.25-2.25Z"
                                              clip-rule="evenodd" />
                                    </svg>
                                    {{ $bug->project?->name ?? '—' }}
                                </span>

                                <span class="text-slate-300" aria-hidden="true">·</span>

                                {{-- Date --}}
                                <span class="inline-flex items-center gap-1 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         class="h-3.5 w-3.5 text-slate-400" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                              d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z"
                                              clip-rule="evenodd" />
                                    </svg>
                                    {{ $dateDisplay }}
                                </span>

                                <span class="text-slate-300" aria-hidden="true">·</span>

                                {{-- Assignee --}}
                                <span class="inline-flex items-center gap-1 {{ $isUnassigned ? 'text-amber-600' : 'text-slate-500' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         class="h-3.5 w-3.5 {{ $isUnassigned ? 'text-amber-400' : 'text-slate-400' }}" aria-hidden="true">
                                        @if ($isUnassigned)
                                            <path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-5.5 6.25A4.75 4.75 0 0 1 9.25 11.5h1.5a4.75 4.75 0 0 1 4.75 4.75.75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" />
                                            <path fill-rule="evenodd"
                                                  d="M16.5 7.75a.75.75 0 0 0-1.5 0v.5h-.5a.75.75 0 0 0 0 1.5h.5v.5a.75.75 0 0 0 1.5 0v-.5h.5a.75.75 0 0 0 0-1.5h-.5v-.5Z"
                                                  clip-rule="evenodd" />
                                        @else
                                            <path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-5.5 6.25A4.75 4.75 0 0 1 9.25 11.5h1.5a4.75 4.75 0 0 1 4.75 4.75.75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" />
                                        @endif
                                    </svg>

                                    @if ($isUnassigned)
                                        <span class="font-medium">Belum ditugaskan</span>
                                    @else
                                        {{ $assigneeName }}
                                    @endif
                                </span>

                            </div>
                        </div>

                        {{-- Right: Chevron --}}
                        <div class="hidden shrink-0 text-slate-300 transition-colors duration-150 group-hover:text-[#8a0b4e] sm:block"
                             aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                 class="h-4 w-4">
                                <path fill-rule="evenodd"
                                      d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z"
                                      clip-rule="evenodd" />
                            </svg>
                        </div>

                    </div>
                </a>

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
                    <p class="text-sm font-semibold text-slate-900">Tidak ada bug ditemukan</p>
                    <p class="mt-1 max-w-xs text-sm text-slate-500">
                        Tidak ada bug yang cocok dengan filter saat ini. Coba ubah atau reset filter.
                    </p>
                    <a
                        href="{{ route('pm.issues.index') }}"
                        class="mt-4 inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:bg-[rgba(138,11,78,0.01)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
                    >
                        Reset Filter
                    </a>
                </div>

            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($bugs->hasPages())
            <div class="border-t border-slate-100 px-6 pb-5 pt-4">
                <x-pagination :paginator="$bugs" />
            </div>
        @endif

    </div>

    {{-- ============================================================
         Filter Submit Handler
         ============================================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterForm      = document.getElementById('pm-issues-filter-form');
            const loadingSkeleton = document.getElementById('pm-issues-loading-skeleton');
            const mainContent     = document.getElementById('pm-issues-main-content');
            const submitButton    = filterForm?.querySelector('[data-filter-submit]');
            const submitLabel     = filterForm?.querySelector('[data-filter-submit-label]');

            if (!filterForm || !loadingSkeleton || !mainContent || !submitButton || !submitLabel) return;

            let isSubmitting = false;

            const showLoading = () => {
                isSubmitting      = true;
                submitButton.disabled  = true;
                submitLabel.textContent = 'Memuat\u2026';
                mainContent.classList.add('hidden');
                loadingSkeleton.classList.remove('hidden');
            };

            const hideLoading = () => {
                isSubmitting      = false;
                submitButton.disabled  = false;
                submitLabel.textContent = 'Terapkan';
                loadingSkeleton.classList.add('hidden');
                mainContent.classList.remove('hidden');
            };

            filterForm.addEventListener('submit', (e) => {
                if (isSubmitting) { e.preventDefault(); return; }

                e.preventDefault();
                showLoading();

                // Fallback: kembalikan UI jika response terlalu lama
                setTimeout(() => { if (isSubmitting) hideLoading(); }, 10_000);

                window.requestAnimationFrame(() => {
                    window.requestAnimationFrame(() => filterForm.submit());
                });
            });

            // Handle browser back-button (page cache)
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) hideLoading();
            });
        });
    </script>

@endsection