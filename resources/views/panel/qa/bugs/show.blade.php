@extends('layouts.qa')

@push('body-attrs')
    data-page="qa-reject"
@endpush

@push('styles')
    @vite(['resources/css/portal-report.css'])

    {{-- Workspace anotasi QA:
         Modal dinamis mengikuti rasio gambar (sama dengan client report)
         Mobile = fullscreen --}}
    <style>
        /* ── Overlay backdrop ─────────────────────────────── */
        #qa-reject-annotation-workspace {
            position: fixed;
            inset: 0;
            z-index: 65;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: flex-start; /* Start from top to allow scrolling */
            justify-content: center;
            padding: 40px 0; /* Desktop padding */
            overflow: auto; /* Support X and Y scrolling */
            -webkit-overflow-scrolling: touch;
        }
        #qa-reject-annotation-workspace.hidden {
            display: none !important;
        }

        /* ── Shell: lebar adaptif, tinggi alami ── */
        #qa-reject-annotation-workspace .report-annotation-shell {
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            border: 1px solid #dde1e7;
            background: #fff;
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 4px 12px rgba(15, 23, 42, 0.04),
                0 24px 64px rgba(15, 23, 42, 0.12);
            width: fit-content; 
            min-width: min(740px, 100vw - 32px);
            max-width: none; 
            overflow: hidden; /* Menjaga rounded corners */
        }

        /* ── Header ──────────────────────────────────────── */
        #qa-reject-annotation-workspace .report-annotation-head {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 14px;
            background: #fafbfc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 12px 12px 0 0;
        }
        #qa-reject-annotation-workspace .report-annotation-file-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(138, 11, 78, 0.07);
            color: #8a0b4e;
            border: 1px solid rgba(138, 11, 78, 0.14);
            flex-shrink: 0;
        }
        #qa-reject-annotation-workspace .report-annotation-file-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            line-height: 1.4;
        }
        #qa-reject-annotation-workspace .report-annotation-close-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border-radius: 6px;
            border: 1px solid #dde1e7;
            background: #fff;
            color: #64748b;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
            transition: border-color 0.15s ease, color 0.15s ease, background-color 0.15s ease;
        }
        #qa-reject-annotation-workspace .report-annotation-close-btn:hover {
            border-color: #c1c7d0;
            color: #1e293b;
            background: #f8fafc;
        }

        /* ── Toolbar ─────────────────────────────────────── */
        #qa-reject-annotation-workspace .report-annotation-toolbar {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 14px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }
        #qa-reject-annotation-workspace .report-annotation-tool-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            color: #64748b;
            border: 1px solid transparent;
            background: #fff;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
        #qa-reject-annotation-workspace .report-annotation-tool-btn:hover:not(:disabled) {
            background: rgba(138, 11, 78, 0.04);
            border-color: rgba(138, 11, 78, 0.14);
            color: #8a0b4e;
        }
        #qa-reject-annotation-workspace .report-annotation-tool-btn.is-active {
            background: #8a0b4e;
            border-color: #8a0b4e;
            color: #fff;
        }
        #qa-reject-annotation-workspace .report-annotation-tool-btn-danger {
            color: #be123c;
        }
        #qa-reject-annotation-workspace .report-annotation-tool-btn-danger:hover:not(:disabled) {
            color: #9f1239;
            border-color: rgba(190, 18, 60, 0.22);
            background: #fff8f9;
        }
        #qa-reject-annotation-workspace .report-annotation-tool-btn:disabled,
        #qa-reject-annotation-workspace .report-annotation-color-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }
        #qa-reject-annotation-workspace .report-annotation-toolbar-divider {
            width: 1px;
            height: 18px;
            background: #e2e8f0;
            margin: 0 4px;
        }

        /* ── Color dots ──────────────────────────────────── */
        #qa-reject-annotation-workspace .report-annotation-color-btn {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.94);
            cursor: pointer;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.12);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        #qa-reject-annotation-workspace .report-annotation-color-btn:hover:not(:disabled) {
            transform: scale(1.1);
        }
        #qa-reject-annotation-workspace .report-annotation-color-btn.is-active {
            box-shadow: 0 0 0 2px #8a0b4e;
            transform: scale(1.1);
        }

        /* ── Save button ─────────────────────────────────── */
        #qa-reject-annotation-workspace .report-annotation-save-btn {
            border-color: #8a0b4e;
            background: #8a0b4e;
            color: #fff;
        }
        #qa-reject-annotation-workspace .report-annotation-save-btn:hover:not(:disabled) {
            background: #6d0940;
            border-color: #6d0940;
        }
        #qa-reject-annotation-workspace .report-annotation-save-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── Status bar ──────────────────────────────────── */
        #qa-reject-annotation-workspace .report-annotation-status {
            flex-shrink: 0;
            font-size: 11px;
            letter-spacing: 0.02em;
            padding: 7px 14px;
            min-height: 32px;
            border-bottom: 1px solid #e2e8f0;
            color: #94a3b8;
            line-height: 1.5;
            background: #fff;
        }
        #qa-reject-annotation-workspace .report-annotation-status.is-success {
            color: #065f46;
        }
        #qa-reject-annotation-workspace .report-annotation-status.is-danger {
            color: #be123c;
        }

        /* ── Canvas area: sama dengan report ── */
        #qa-reject-annotation-workspace .report-annotation-canvas {
            position: relative;
            width: 100%;
            background: #f0f2f5;
            overflow: visible; /* Hindari scroll internal */
        }

        #qa-reject-annotation-workspace .report-annotation-canvas .konvajs-content {
            border-radius: 0;
            box-shadow: none;
        }

        /* ── Mobile: fullscreen ──────────────────────────── */
        @media (max-width: 640px) {
            #qa-reject-annotation-workspace {
                padding: 0;
                display: block; /* Hindari flex-center agar wide content tdk terpotong */
                overflow-x: auto;
                overflow-y: auto;
            }

            #qa-reject-annotation-workspace.is-landscape-mode {
                overflow-y: hidden; /* Kunci vertikal jika mode landscape */
            }

            #qa-reject-annotation-workspace .report-annotation-shell {
                margin: 0 auto;
                border-radius: 0;
                width: fit-content !important;
                min-width: 100vw;
                height: 100dvh !important;
                max-width: none;
                max-height: none;
                border: none;
                box-shadow: none;
            }

            #qa-reject-annotation-workspace .report-annotation-head {
                border-radius: 0;
                padding: 10px 12px;
                flex-wrap: wrap;
                gap: 8px;
            }

            #qa-reject-annotation-workspace .report-annotation-close-btn span {
                display: none;
            }

            #qa-reject-annotation-workspace .report-annotation-toolbar {
                padding: 8px 12px;
                gap: 3px;
            }

            #qa-reject-annotation-workspace .report-annotation-canvas {
                padding: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/portal-report-annotator.js'])
@endpush

@push('body-attrs')
    data-page="qa-reject"
@endpush

@section('title', 'QA - Detail Tiket')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Back behavior + breadcrumb dinamis
        |--------------------------------------------------------------------------
        */
        $currentUrl = url()->current();
        $rawReturn  = (string) request()->query('return', '');
        $appUrl     = config('app.url');
        $appHost    = parse_url($appUrl, PHP_URL_HOST);

        $isSafeReturn = false;
        if ($rawReturn !== '') {
            $decoded      = urldecode($rawReturn);
            $returnHost   = parse_url($decoded, PHP_URL_HOST);
            $returnScheme = parse_url($decoded, PHP_URL_SCHEME);

            if ($returnScheme === null && $returnHost === null && str_starts_with($decoded, '/')) {
                $isSafeReturn = true;
            }

            if (! $isSafeReturn && $returnHost && $appHost && $returnHost === $appHost) {
                $isSafeReturn = true;
            }

            if ($isSafeReturn && rtrim($decoded, '/') === rtrim($currentUrl, '/')) {
                $isSafeReturn = false;
            }
        }

        $backUrl = $isSafeReturn ? urldecode($rawReturn) : route('qa.testing-queue');

        $returnLabel = 'Antrian Pengujian';
        if ($backUrl) {
            if (str_contains($backUrl, 'notifications')) {
                $returnLabel = 'Notifikasi';
            } elseif (str_contains($backUrl, 'testing-queue')) {
                $returnLabel = 'Antrian Pengujian';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Split deskripsi + langkah reproduksi
        |--------------------------------------------------------------------------
        */
        $rawDescription    = (string) ($bug->description ?? '');
        $marker            = 'Langkah Reproduksi:';
        $descriptionText   = $rawDescription;
        $reproductionSteps = '';

        if (str_contains($rawDescription, $marker)) {
            [$descPart, $reproPart] = array_pad(explode($marker, $rawDescription, 2), 2, '');
            $descriptionText   = trim($descPart);
            $reproductionSteps = trim($reproPart);
        }

        /*
        |--------------------------------------------------------------------------
        | Ticket label
        |--------------------------------------------------------------------------
        */
        $ticketLabel = $ticket ?? $bug->ticket ?? sprintf('BUG-%06d', $bug->id);

        /*
        |--------------------------------------------------------------------------
        | Timeline helpers
        |--------------------------------------------------------------------------
        */
        $timelineKey = fn ($s) => str((string) $s)->lower()->replace(' ', '_')->toString();

        $timelineLabel = fn ($s) => match ($s) {
            'reported'    => 'Dilaporkan',
            'assigned'    => 'Ditugaskan',
            'in_progress' => 'Dalam Pengerjaan',
            'testing'     => 'Pengujian',
            'resolved'    => 'Diselesaikan',
            'closed'      => 'Ditutup',
            'rejected'    => 'Dikembalikan QA',
            default       => ucfirst(str_replace('_', ' ', (string) $s)),
        };

        $timelineDot = fn ($s) => match ($s) {
            'reported'    => 'bg-amber-500',
            'assigned'    => 'bg-sky-500',
            'in_progress' => 'bg-blue-500',
            'testing'     => 'bg-violet-500',
            'resolved'    => 'bg-emerald-500',
            'closed'      => 'bg-slate-500',
            'rejected'    => 'bg-rose-500',
            default       => 'bg-slate-300',
        };

        $timelineLine = fn ($s) => match ($s) {
            'reported'    => 'bg-amber-200',
            'assigned'    => 'bg-sky-200',
            'in_progress' => 'bg-blue-200',
            'testing'     => 'bg-violet-200',
            'resolved'    => 'bg-emerald-200',
            'closed'      => 'bg-slate-200',
            'rejected'    => 'bg-rose-200',
            default       => 'bg-slate-200',
        };

        $histories = ($bug->statusHistories ?? collect())->sortBy('changed_at')->values();
        $events    = collect();

        $events->push([
            'status' => $histories->first()?->old_status ?? $bug->status,
            'at'     => $bug->created_at,
        ]);

        foreach ($histories as $h) {
            $events->push([
                'status' => $h->new_status,
                'at'     => $h->changed_at,
                'is_revision' => ($h->old_status === 'Testing' && $h->new_status === 'In Progress'),
            ]);
        }

        if (($events->last()['status'] ?? null) !== $bug->status) {
            $events->push([
                'status' => $bug->status,
                'at'     => $bug->updated_at,
            ]);
        }

        $events = $events->values();
    @endphp

    <div 
        x-data="{
            ...bugWorkflowSection({ 
                csrf: '{{ csrf_token() }}', 
                initialBugStatus: '{{ $bug->status }}',
                initialTicket: '{{ $ticketLabel }}'
            }),
            
            statusLabelUi(status) {
                const map = {
                    'Reported':    'Dilaporkan',
                    'Assigned':    'Ditugaskan',
                    'In Progress': 'Dalam Pengerjaan',
                    'Testing':     'Pengujian',
                    'Resolved':    'Diselesaikan',
                    'Closed':      'Ditutup',
                    'Rejected':    'Dikembalikan QA',
                };
                return map[status] || status || '-';
            },

            statusBadgeUi(status) {
                const map = {
                    'Reported':    { bg: 'bg-amber-50',   text: 'text-amber-700',   dot: 'bg-amber-500' },
                    'Assigned':    { bg: 'bg-sky-50',     text: 'text-sky-700',     dot: 'bg-sky-500' },
                    'In Progress': { bg: 'bg-blue-50',    text: 'text-blue-700',    dot: 'bg-blue-500' },
                    'Testing':     { bg: 'bg-violet-50',  text: 'text-violet-700',  dot: 'bg-violet-500' },
                    'Resolved':    { bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500' },
                    'Closed':      { bg: 'bg-slate-100',  text: 'text-slate-700',   dot: 'bg-slate-500' },
                    'Rejected':    { bg: 'bg-rose-50',    text: 'text-rose-700',    dot: 'bg-rose-500' },
                };
                return map[status] || { bg: 'bg-slate-100', text: 'text-slate-700', dot: 'bg-slate-500' };
            },
        }"
    >
    {{-- ============================================================
         Header Halaman
         ============================================================ --}}
    <div class="mb-8">
        <nav class="mb-4 flex items-center gap-1.5" aria-label="Breadcrumb">
            <a
                href="{{ $backUrl }}"
                class="text-xs text-slate-400 transition-colors hover:text-[#8a0b4e]"
            >
                {{ $returnLabel }}
            </a>

            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                 class="h-3 w-3 shrink-0 text-slate-300" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                      clip-rule="evenodd" />
            </svg>

            <span class="text-xs font-medium text-slate-600" aria-current="page">Detail Tiket</span>
        </nav>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="font-mono text-[11px] font-semibold tracking-[0.08em] text-slate-700">
                        {{ $ticketLabel }}
                    </span>

                    @if ($bug->project?->name)
                        <span class="inline-flex items-center rounded-full border border-slate-300/80 bg-slate-50 px-2.5 py-0.5 text-[10px] font-medium text-slate-600">
                            {{ $bug->project->name }}
                        </span>
                    @endif

                    @if ($bug->severity)
                        <x-severity-badge :severity="$bug->severity" class="px-2 py-0.5 text-[10px] font-semibold" />
                    @endif
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-slate-800 sm:text-[28px]">
                    {{ $bug->title }}
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">
                    Periksa hasil perbaikan programmer, lalu setujui atau kembalikan tiket ini dengan catatan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold"
                    x-bind:class="statusBadgeUi(status).bg + ' ' + statusBadgeUi(status).text"
                >
                    <span class="h-1.5 w-1.5 rounded-full" x-bind:class="statusBadgeUi(status).dot"></span>
                    <span x-text="statusLabelUi(status)"></span>
                </span>

                @if ($bug->priority)
                    <x-priority-badge :priority="$bug->priority" class="px-2.5 py-1 text-[11px]" />
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Belum diprioritaskan
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================
         Content Grid
         ============================================================ --}}
    <div 
        class="grid grid-cols-1 gap-8 lg:grid-cols-3"
    >
        {{-- Sisi Kiri: Detail & Komentar --}}
        <div class="space-y-8 lg:col-span-2">
            {{-- Status & Actions --}}
            <section 
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                x-show="status === 'Testing'"
            >
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Tindakan QA</p>
                </div>
                <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                    <div class="p-6">
                        <p class="text-sm font-semibold text-slate-800">Setujui Penyelesaian</p>
                        <p class="mt-1 text-xs text-slate-500">Tandai bug ini sebagai sudah diperbaiki dengan benar.</p>
                        <div class="mt-4">
                            <form @submit.prevent="postJson('{{ route('qa.bugs.approve', $bug) }}')">
                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="inline-flex h-8 w-full items-center justify-center rounded-lg bg-[#8a0b4e] px-4 text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span x-show="!submitting">Selesaikan Bug (Approve)</span>
                                    <span x-show="submitting" x-cloak>Memproses...</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-sm font-semibold text-slate-800">Kembalikan (Reject)</p>
                        <p class="mt-1 text-xs text-slate-500">Jika perbaikan belum sesuai atau muncul masalah baru.</p>
                        <div class="mt-4">
                            <a
                                href="#qa-reject-form"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-rose-200 hover:text-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-100 focus-visible:ring-offset-1"
                            >
                                Berikan Alasan Penolakan
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Laporan --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Laporan
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Detail Laporan</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Informasi konteks tiket yang dibutuhkan untuk proses validasi QA.
                    </p>
                </div>

                <div class="space-y-6 px-6 py-5">
                    <div>
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                            Deskripsi
                        </p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">
                            {{ $descriptionText }}
                        </p>
                    </div>

                    @if ($reproductionSteps !== '')
                        <div class="border-t border-slate-100 pt-5">
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                                Langkah Reproduksi
                            </p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">
                                {{ $reproductionSteps }}
                            </p>
                        </div>
                    @endif

                    <div class="border-t border-slate-100 pt-5">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                            Lampiran
                        </p>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @forelse ($bug->attachments->filter(fn($a) => is_null($a->comment_id) && is_null($a->uploaded_by)) as $file)
                                @php
                                    $fileName  = (string) ($file->file_name ?? 'file');
                                    $fileType  = strtolower((string) ($file->file_type ?? ''));
                                    $isImage   = str_starts_with($fileType, 'image/') || preg_match('/\.(png|jpe?g|gif|webp)$/i', $fileName);
                                    $publicUrl = isset($file->file_path) ? asset('storage/' . $file->file_path) : null;
                                @endphp

                                <a
                                    href="{{ $publicUrl ?? '#' }}"
                                    @if ($publicUrl) target="_blank" rel="noopener" @endif
                                    class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition-colors duration-150 hover:border-[rgba(138,11,78,0.18)] hover:bg-[rgba(138,11,78,0.01)]"
                                    title="{{ $fileName }}"
                                >
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                        @if ($isImage && $publicUrl)
                                            <img src="{{ $publicUrl }}" alt="{{ $fileName }}" class="h-full w-full object-cover" loading="lazy" />
                                        @else
                                            <span class="text-lg">📄</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-slate-800 transition-colors group-hover:text-[#8a0b4e]">
                                            {{ $fileName }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ $file->file_size ? $file->file_size . ' KB' : '' }}
                                            @if (! empty($fileType))
                                                <span class="text-slate-300">·</span> {{ $fileType }}
                                            @endif
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-slate-500">Tidak ada lampiran.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
            
            {{-- Revisi (QA Rejection) --}}
            @php
                $rejectionComments = $bug->comments->where('type', 'rejection')->sortBy('created_at');
            @endphp

            @if ($rejectionComments->count() > 0)
                <section class="overflow-visible rounded-2xl border border-rose-200 bg-white shadow-sm">
                    <div class="rounded-t-2xl border-b border-rose-100 bg-rose-50/30 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-rose-600/80">
                                    Pusat Revisi
                                </p>
                                <p class="mt-1 text-sm font-semibold text-slate-800">Riwayat Penolakan QA</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-rose-700">
                                {{ $rejectionComments->count() }} Siklus Penolakan
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            Riwayat instruksi perbaikan yang pernah dikirimkan ke programmer.
                        </p>
                    </div>

                    <div class="divide-y divide-rose-100">
                        @foreach ($rejectionComments as $index => $rev)
                            <div class="px-6 py-6 transition-colors hover:bg-rose-50/10">
                                <div class="flex items-start gap-4">
                                    <div 
                                        class="relative inline-flex"
                                        x-data="{ open: false }"
                                        @mouseenter="open = true"
                                        @mouseleave="open = false"
                                    >
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 shadow-sm cursor-help">
                                            <span class="text-xs font-bold">#{{ $loop->iteration }}</span>
                                        </div>

                                        <div
                                            x-cloak
                                            x-show="open"
                                            x-transition:enter="transition duration-150 ease-out"
                                            x-transition:enter-start="opacity-0 translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition duration-100 ease-in"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 translate-y-1"
                                            class="absolute bottom-full left-1/2 z-20 mb-2 w-max -translate-x-1/2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 shadow-xl"
                                        >
                                            Siklus Penolakan #{{ $loop->iteration }}
                                            <div class="absolute -bottom-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 border-b border-r border-slate-200 bg-white"></div>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-slate-800">{{ $rev->user?->name }}</span>
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500">QA Specialist (You)</span>
                                            </div>
                                            <span class="text-[11px] text-slate-400">{{ $rev->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i') }}</span>
                                        </div>

                                        <div class="prose prose-sm max-w-none text-slate-700">
                                            <p class="whitespace-pre-line leading-relaxed">{{ $rev->content }}</p>
                                        </div>

                                        {{-- Lampiran Revisi --}}
                                        @if ($rev->attachments->count() > 0)
                                            <div class="mt-4 flex flex-wrap gap-3">
                                                @foreach ($rev->attachments as $att)
                                                    @php
                                                        $attName = (string) ($att->file_name ?? 'file');
                                                        $attType = strtolower((string) ($att->file_type ?? ''));
                                                        $isAttImage = str_starts_with($attType, 'image/') || preg_match('/\.(png|jpe?g|gif|webp)$/i', $attName);
                                                        $attUrl = asset('storage/' . $att->file_path);
                                                    @endphp
                                                    <a 
                                                        href="{{ $attUrl }}" 
                                                        target="_blank" 
                                                        class="group relative flex h-24 w-32 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-slate-50 transition-all hover:border-rose-300 hover:ring-2 hover:ring-rose-50"
                                                        title="{{ $attName }}"
                                                    >
                                                        @if ($isAttImage)
                                                            <img src="{{ $attUrl }}" alt="{{ $attName }}" class="h-full w-full object-cover transition-transform group-hover:scale-105">
                                                            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/60 to-transparent p-1.5 opacity-0 transition-opacity group-hover:opacity-100">
                                                                <span class="truncate text-[9px] text-white">{{ $attName }}</span>
                                                            </div>
                                                        @else
                                                            <div class="flex flex-col items-center gap-1 p-2 text-center">
                                                                <span class="text-2xl">📄</span>
                                                                <span class="line-clamp-1 text-[9px] font-medium text-slate-600">{{ $attName }}</span>
                                                            </div>
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Komentar --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Diskusi
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Komentar</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Catat temuan pengujian, catatan validasi, dan hasil verifikasi agar riwayat QA tetap terdokumentasi.
                    </p>
                </div>

                <div class="px-6 py-5">
                    <div
                        x-data="{
                            ...pmCommentSection({
                                postUrl: '{{ route('qa.bugs.comments.store', $bug) }}',
                                csrf: '{{ csrf_token() }}',
                                initialComments: {{ $bug->comments->where('type', 'discussion')->map(fn($c) => [
                                    'id'           => $c->id,
                                    'content'      => $c->content,
                                    'user_name'    => $c->user?->name,
                                    'user_initial' => strtoupper(substr($c->user?->name ?? 'U', 0, 1)),
                                    'created_at'   => $c->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'),
                                ])->values()->toJson() }},
                            }),
                            showEmptyAlert: false,
                        }"
                        class="space-y-4"
                    >
                        <template x-if="comments.length === 0">
                            <div class="py-6 text-center">
                                <p class="text-sm text-slate-400">
                                    Belum ada komentar.
                                    <a href="#comment-form" class="font-medium text-slate-600 underline-offset-2 transition-colors hover:text-[#8a0b4e] hover:underline">
                                        Tulis komentar pertama.
                                    </a>
                                </p>
                            </div>
                        </template>

                        <template x-for="g in groupedComments" :key="`${g.user_name}-${g.created_at}-${g.items?.[0]?.id || '0'}`">
                            <div class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white"
                                    style="background-color: #8a0b4e;"
                                    x-text="g.user_initial"
                                ></div>

                                <div class="min-w-0 flex-1">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-medium text-slate-800" x-text="g.user_name"></span>
                                        <span class="text-xs text-slate-400" x-text="g.created_at"></span>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="item in g.items" :key="item.id">
                                            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600" x-text="item.content"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="border-t border-slate-100 pt-5">
                            <form
                                id="comment-form"
                                method="POST"
                                action="{{ route('qa.bugs.comments.store', $bug) }}"
                                class="space-y-3"
                                @submit.prevent="submit()"
                            >
                                @csrf

                                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400" for="qa-content">
                                    Tambah Komentar
                                </label>

                                <textarea
                                    id="qa-content"
                                    name="content"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                                    placeholder="Tulis temuan pengujian, catatan hasil validasi, atau skenario yang belum lolos…"
                                    x-model="content"
                                    :disabled="submitting"
                                    x-on:input="if (content.trim()) showEmptyAlert = false"
                                ></textarea>

                                <p class="text-xs text-rose-500" x-show="showEmptyAlert" x-transition.opacity x-cloak>
                                    Kolom komentar wajib diisi sebelum mengirim.
                                </p>

                                <p class="text-xs text-red-600" x-show="error" x-text="error"></p>

                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] text-slate-400" x-text="`${(content || '').length}/5000`"></p>

                                    <button
                                        type="submit"
                                        class="inline-flex h-8 items-center justify-center rounded-lg bg-[#8a0b4e] px-4 text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] disabled:cursor-not-allowed disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                                        :disabled="submitting"
                                    >
                                        <span x-show="!submitting">Kirim Komentar</span>
                                        <span x-show="submitting" x-cloak>Mengirim…</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

                            {{-- Secondary: Kembalikan + Catatan + Lampiran --}}
                            <form
                                id="qa-reject-form"
                                method="POST"
                                action="{{ route('qa.bugs.reject', $bug) }}"
                                enctype="multipart/form-data"
                                class="space-y-4"
                            >
                                @csrf

                                {{-- Catatan alasan --}}
                                <div>
                                    <label
                                        class="block font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400"
                                        for="reason"
                                    >
                                        Catatan untuk Programmer
                                    </label>
            <section
                id="qa-reject-form-section"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                x-show="status === 'Testing'"
            >
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">Penolakan</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Form Pengembalian Bug</p>
                </div>

                <div class="px-6 py-5">
                    <template x-if="status === 'Testing'">
                        <form
                            id="qa-reject-form"
                            action="{{ route('qa.bugs.reject', $bug) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="space-y-4"
                            @submit.prevent="postForm($el.action, new FormData($el)).then(res => { if(res) window.location.reload(); })"
                        >
                            @csrf

                            {{-- Alasan Penolakan --}}
                            <div>
                                <label for="reason" class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                                    Alasan Penolakan / Catatan Revisi
                                </label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="4"
                                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/30 px-3 py-2.5 text-sm text-slate-700 transition-colors focus:border-[rgba(138,11,78,0.30)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[rgba(138,11,78,0.05)]"
                                    placeholder="Jelaskan bagian mana yang belum sesuai atau temuan baru…"
                                ></textarea>
                            </div>

                            {{-- Lampiran Gambar --}}
                            <div>
                                <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                                    Lampiran Gambar
                                    <span class="ml-1 font-sans normal-case tracking-normal text-slate-300">(opsional, maks. 5)</span>
                                </p>

                                {{-- Input file tersembunyi --}}
                                <input
                                    id="qa-reject-attachments"
                                    name="attachments[]"
                                    type="file"
                                    multiple
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    class="sr-only"
                                    aria-label="Lampirkan gambar"
                                />

                                {{-- Dropzone --}}
                                <div
                                    id="qa-reject-dropzone"
                                    class="report-upload-dropzone mt-1.5 !rounded-xl"
                                    role="button"
                                    tabindex="0"
                                    aria-controls="qa-reject-attachments"
                                >
                                    <div id="qa-reject-dropzone-empty" class="report-upload-empty-state">
                                        <span class="report-upload-empty-icon" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><polyline stroke-linecap="round" stroke-linejoin="round" points="7 9 12 4 17 9"/><line stroke-linecap="round" stroke-linejoin="round" x1="12" y1="4" x2="12" y2="16"/></svg>
                                        </span>
                                        <p class="report-upload-empty-title">Seret gambar ke sini atau klik untuk memilih</p>
                                        <p class="report-upload-empty-subtitle">JPG, PNG, WEBP, GIF — maks. 5 file · 5 MB per file</p>
                                    </div>
                                </div>

                                {{-- Preview + Annotasi --}}
                                <div id="qa-reject-preview-wrapper" class="report-attachment-preview-shell hidden mt-2">
                                    <div class="report-attachment-preview-head">
                                        <div class="report-attachment-preview-head-top">
                                            <p class="report-attachment-preview-title">Terlampir</p>
                                            <button
                                                type="button"
                                                id="qa-reject-attachment-add-btn"
                                                class="report-preview-btn report-preview-btn-ghost report-attachment-add-btn hidden"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                <span>Tambah</span>
                                            </button>
                                        </div>
                                        <p class="report-field-hint">Klik Anotasi untuk menandai area bermasalah.</p>
                                    </div>

                                    <div id="qa-reject-preview-list" class="report-attachment-preview-list"></div>

                                    {{-- Workspace Anotasi --}}
                                    <div id="qa-reject-annotation-workspace" class="hidden">
                                        <div class="report-annotation-shell">
                                            <div class="report-annotation-head">
                                                <div class="report-annotation-file">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 report-annotation-file-icon" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/></svg>
                                                    <span id="qa-reject-annotation-file-label" class="report-annotation-file-label"></span>
                                                </div>
                                                <button id="qa-reject-annotation-close" type="button" class="report-annotation-close-btn">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    <span>Tutup</span>
                                                </button>
                                            </div>

                                            <div
                                                id="qa-reject-annotation-toolbar"
                                                class="report-annotation-toolbar"
                                                role="toolbar"
                                                aria-label="Toolbar Anotasi"
                                            >
                                                <div class="report-annotation-toolbar-group">
                                                    <button type="button" data-tool="select"    class="report-annotation-tool-btn is-active" title="Pilih"     disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4 4 7 18 3-7 7-3Z"/></svg></button>
                                                    <button type="button" data-tool="rectangle" class="report-annotation-tool-btn" title="Kotak"     disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg></button>
                                                    <button type="button" data-tool="arrow"     class="report-annotation-tool-btn" title="Panah"     disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><line x1="5" y1="19" x2="19" y2="5"/><polyline points="9 5 19 5 19 15"/></svg></button>
                                                    <button type="button" data-tool="freehand"  class="report-annotation-tool-btn" title="Coretan"   disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5c-1.5 0-3-1-3-2.5C9 15.5 10.5 14 12 14s3 1 3 1.5V6a1.5 1.5 0 0 0-3 0"/></svg></button>
                                                    <button type="button" data-tool="text"      class="report-annotation-tool-btn" title="Teks"      disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg></button>
                                                </div>

                                                <div class="report-annotation-toolbar-divider"></div>

                                                <div class="report-annotation-toolbar-group">
                                                    <button type="button" data-color="#EF4444" class="report-annotation-color-btn is-active" style="background:#EF4444" title="Merah"  disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#F59E0B" class="report-annotation-color-btn"           style="background:#F59E0B" title="Kuning" disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#10B981" class="report-annotation-color-btn"           style="background:#10B981" title="Hijau"  disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#3B82F6" class="report-annotation-color-btn"           style="background:#3B82F6" title="Biru"   disabled aria-disabled="true"></button>
                                                    <button type="button" data-color="#111827" class="report-annotation-color-btn"           style="background:#111827" title="Hitam"  disabled aria-disabled="true"></button>
                                                </div>

                                                <div class="report-annotation-toolbar-divider"></div>

                                                <div class="report-annotation-toolbar-group ml-auto">
                                                    <button type="button" id="qa-reject-annotation-undo"      class="report-annotation-tool-btn" title="Undo"       disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg></button>
                                                    <button type="button" id="qa-reject-annotation-redo"      class="report-annotation-tool-btn" title="Redo"       disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><polyline points="15 14 20 9 15 4"/><path d="M4 20v-7a4 4 0 0 1 4-4h12"/></svg></button>
                                                    <button type="button" id="qa-reject-annotation-delete"    class="report-annotation-tool-btn report-annotation-tool-btn-danger" title="Hapus dipilih" disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                                                    <button type="button" id="qa-reject-annotation-clear-all" class="report-annotation-tool-btn report-annotation-tool-btn-danger" title="Hapus semua" disabled aria-disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 2H2v10l9.29 9.29a1 1 0 0 0 1.41 0l6.59-6.59a1 1 0 0 0 0-1.41Z"/><path d="M7 7h.01"/></svg></button>
                                                    <button type="button" id="qa-reject-annotation-save"      class="report-preview-btn report-preview-btn-solid report-annotation-save-btn" title="Simpan anotasi" disabled aria-disabled="true">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                        <span>Simpan</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="qa-reject-annotation-status" class="report-annotation-status"></div>
                                            <div id="qa-reject-annotation-canvas" class="report-annotation-canvas"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="!submitting">Kembalikan ke Programmer</span>
                                <span x-show="submitting" x-cloak>Mengirim...</span>
                            </button>
                        </form>
                    </template>

                    <div x-show="status !== 'Testing'" x-cloak>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Tindakan validasi hanya tersedia saat tiket berada pada status Pengujian.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Ringkasan --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Info
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Ringkasan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Pelapor</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->guest_name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $bug->guest_email ?? '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Penanggung Jawab</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->assignee?->name ?? '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Versi Aplikasi</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->guest_version ?? '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Dilaporkan Pada</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Terakhir Diperbarui</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->updated_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- Prioritas & SLA --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Prioritas
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Prioritas & SLA</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Ditetapkan oleh Project Manager sebagai acuan penanganan.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                            Prioritas Saat Ini
                        </p>
                        <div class="mt-1.5">
                            @if ($bug->priority)
                                <x-priority-badge :priority="$bug->priority" class="px-2.5 py-1 text-[11px]" />
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    Belum diprioritaskan
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($bug->priority)
                        <div class="px-6 py-4">
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">
                                Target SLA
                            </p>
                            <p class="mt-1.5 text-sm font-medium text-slate-800">
                                {{ $bug->priority->sla_hours }} jam
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Timeline --}}
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                x-data="bugTimelineSection({
                    initialEvents: {{ collect($events)->map(fn($e) => [
                        'status' => $e['status'],
                        'at' => $e['at'] instanceof \DateTime ? $e['at']->format('d M Y, H:i') : $e['at'],
                        'is_revision' => $e['is_revision'] ?? false,
                    ])->toJson() }}
                })"
            >
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Riwayat
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Status Timeline</p>
                </div>

                <div class="px-6 py-5">
                    <template x-if="events.length === 0">
                        <p class="text-sm text-slate-500">Belum ada riwayat perubahan status.</p>
                    </template>

                    <template x-for="(e, index) in events" :key="index">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full" :class="timelineDot(e.status, e.is_revision)"></div>
                                <template x-if="index < events.length - 1">
                                    <div class="mt-1 w-px flex-1" :class="timelineLine(e.status, e.is_revision)" style="min-height:24px"></div>
                                </template>
                            </div>

                            <div class="min-w-0 flex-1" :class="index === events.length - 1 ? 'pb-0' : 'pb-4'">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-slate-800" x-text="timelineLabel(e.status)"></p>
                                    <template x-if="e.is_revision">
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-rose-600 border border-rose-100">
                                            Revisi
                                        </span>
                                    </template>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400" x-text="e.at"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </section>
        </div>
    </div>
</div>

@endsection