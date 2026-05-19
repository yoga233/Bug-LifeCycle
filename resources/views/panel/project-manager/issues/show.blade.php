{{-- resources/views/panel/project-manager/issues/show.blade.php --}}
@extends('layouts.project-manager')

@section('title', 'Detail Tiket')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Back behavior + breadcrumb dinamis
        |--------------------------------------------------------------------------
        */
        $currentUrl = url()->current();
        $rawReturn  = (string) request()->query('return', '');
        $appHost    = parse_url(config('app.url'), PHP_URL_HOST);

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

        $backUrl = $isSafeReturn ? urldecode($rawReturn) : route('pm.dashboard');

        $returnLabel = 'Dashboard';
        if ($backUrl) {
            if (str_contains($backUrl, 'kinerja')) {
                $returnLabel = 'Riwayat Kinerja';
            } elseif (str_contains($backUrl, 'issues')) {
                $returnLabel = 'Semua Tiket';
            } elseif (str_contains($backUrl, 'management')) {
                $returnLabel = 'Manajemen';
            } elseif (str_contains($backUrl, 'notifications')) {
                $returnLabel = 'Notifikasi';
            } elseif (str_contains($backUrl, 'dashboard')) {
                $returnLabel = 'Dashboard';
            }
        }

        $canAssign       = in_array($bug->status, ['Reported', 'Assigned'], true);
        $canEditPriority = $bug->status === 'Reported';

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
        | Parse SLA dari title
        |--------------------------------------------------------------------------
        */
        $rawTitle         = (string) ($bug->title ?? '');
        $titleDisplay     = $rawTitle;
        $titleSuffix      = '';
        $titleSuffixClass = 'text-slate-400';

        if (preg_match('/\s*-\s*(SLA\s+(?:Terlambat|Tepat|Terlewat)[^-]*?)(?:\s*-\s*|$)/iu', $rawTitle, $m)) {
            $titleSuffix  = trim((string) $m[1]);
            $titleDisplay = trim(str_replace($m[0], ' - ', $rawTitle), ' -');
            $titleDisplay = preg_replace('/\s*-\s*-\s*/', ' - ', $titleDisplay);
            $titleDisplay = trim((string) $titleDisplay, ' -');
            $titleSuffix  = preg_replace('/^SLA\s+/iu', '', $titleSuffix);

            $suffixLower = mb_strtolower($titleSuffix);
            if (str_contains($suffixLower, 'terlambat') || str_contains($suffixLower, 'terlewat')) {
                $titleSuffixClass = 'text-amber-600';
            } elseif (str_contains($suffixLower, 'tepat')) {
                $titleSuffixClass = 'text-emerald-600';
            }
        }

        $ticketLabel = $ticket ?? $bug->ticket ?? sprintf('BUG-%06d', $bug->id);

        /*
        |--------------------------------------------------------------------------
        | Timeline helpers
        |--------------------------------------------------------------------------
        */
        $timelineKey = fn ($s) => str($s)->lower()->replace(' ', '_')->toString();

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
            if (($events->last()['status'] ?? null) === $h->new_status) {
                continue;
            }
            $events->push([
                'status' => $h->new_status,
                'at' => $h->changed_at,
                'is_revision' => ($h->old_status === 'Testing' && $h->new_status === 'In Progress'),
            ]);
        }
        if (($events->last()['status'] ?? null) !== $bug->status) {
            $events->push(['status' => $bug->status, 'at' => $bug->updated_at]);
        }
        $events = $events->values();
    @endphp

    <div
        x-data="{
            ...pmAssignmentSection({
                assignUrl: '{{ route('pm.issues.assign', $bug) }}',
                unassignUrl: '{{ route('pm.issues.unassign', $bug) }}',
                updatePriorityUrl: '{{ route('pm.issues.priority.update', $bug) }}',
                csrf: '{{ csrf_token() }}',
                canAssign: @js($canAssign),
                canEditPriority: @js($canEditPriority),
                initialPriority: @js($bug->priority ? [
                    'id'         => $bug->priority->id,
                    'level'      => $bug->priority->level,
                    'sla_hours'  => $bug->priority->sla_hours,
                    'bg_color'   => $bug->priority->bg_color,
                    'text_color' => $bug->priority->text_color,
                ] : null),
                initialAssigneeName: @js($bug->assignee?->name ?? '—'),
                initialBug: @js([
                    'id'          => $bug->id,
                    'title'       => $titleDisplay,
                    'ticket'      => $ticketLabel,
                    'status'      => $bug->status,
                    'assignee_id' => $bug->assignee_id,
                ]),
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
        x-init="init()"
        x-on:pm-confirm-assign.window="confirmAssign()"
        x-on:pm-confirm-unassign.window="confirmUnassign()"
    >

        {{-- ============================================================
             Header
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

                {{-- Kiri --}}
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
                        <span>{{ $titleDisplay }}</span>
                        @if ($titleSuffix !== '')
                            <span class="ml-1.5 text-sm font-medium {{ $titleSuffixClass }}">
                                — {{ $titleSuffix }}
                            </span>
                        @endif
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">
                        Tinjau laporan, tetapkan prioritas, dan tugaskan programmer yang akan menangani tiket ini.
                    </p>
                </div>

                {{-- Kanan --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold"
                        x-bind:class="statusBadgeUi(bug.status).bg + ' ' + statusBadgeUi(bug.status).text"
                    >
                        <span class="h-1.5 w-1.5 rounded-full" x-bind:class="statusBadgeUi(bug.status).dot"></span>
                        <span x-text="statusLabelUi(bug.status)"></span>
                    </span>

                    <template x-if="priority">
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide leading-none whitespace-nowrap"
                            x-bind:style="priorityBadgeStyle(priority)"
                            x-text="priority.level"
                        ></span>
                    </template>

                    <template x-if="!priority">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide leading-none whitespace-nowrap text-slate-500">
                            Belum diprioritaskan
                        </span>
                    </template>
                </div>
            </div>
        </div>

        {{-- ============================================================
             Content Grid
             ============================================================ --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Laporan --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                            Laporan
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">Detail Laporan</p>
                        <p class="mt-1 text-sm text-slate-500">
                            Informasi utama yang dibutuhkan untuk analisa, penugasan, dan tindak lanjut.
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
                                        Audit Trail
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">Riwayat Revisi & Penolakan</p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-rose-700">
                                    {{ $rejectionComments->count() }} Siklus Penolakan
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">
                                Rekam jejak penolakan QA untuk keperluan audit dan monitoring kualitas.
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
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500">QA Specialist</span>
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
                            Diskusi internal antara PM, Programmer, dan QA terkait tiket ini.
                        </p>
                    </div>

                    <div class="px-6 py-5">
                        <div
                            x-data="{
                                ...pmCommentSection({
                                    postUrl: '{{ route('pm.issues.comments.store', $bug) }}',
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
                                isWritingFirstComment: false,
                            }"
                            class="space-y-4"
                        >
                            <template x-if="comments.length === 0 && !isWritingFirstComment">
                                <div class="py-6 text-center">
                                    <p class="text-sm text-slate-400">
                                        Belum ada komentar.
                                        <a href="#comment-form" @click.prevent="isWritingFirstComment = true; $nextTick(() => $refs.commentTextarea.focus())" class="font-medium text-slate-600 underline-offset-2 transition-colors hover:text-[#8a0b4e] hover:underline">
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

                            <div class="border-t border-slate-100 pt-5" x-show="comments.length > 0 || isWritingFirstComment">
                                <form
                                    id="comment-form"
                                    method="POST"
                                    action="{{ route('pm.issues.comments.store', $bug) }}"
                                    class="space-y-3"
                                    @submit.prevent
                                >
                                    @csrf

                                    <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400" for="content">
                                        Tambah Komentar
                                    </label>

                                    <textarea
                                        id="content"
                                        name="content"
                                        rows="3"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[rgba(138,11,78,0.35)] focus:outline-none focus:ring-2 focus:ring-[rgba(138,11,78,0.10)]"
                                        placeholder="Tulis konteks, keputusan, atau update untuk tim…"
                                        x-model="content"
                                        x-ref="commentTextarea"
                                        :disabled="submitting"
                                        x-on:input="if (content.trim()) showEmptyAlert = false"
                                        @blur="if (!content.trim()) isWritingFirstComment = false"
                                    ></textarea>

                                    <p class="text-xs text-rose-500" x-show="showEmptyAlert" x-transition.opacity x-cloak>
                                        Kolom komentar wajib diisi sebelum mengirim.
                                    </p>

                                    <p class="text-xs text-red-600" x-show="error" x-text="error"></p>

                                    <div class="flex items-center justify-between">
                                        <p class="text-[11px] text-slate-400" x-text="`${(content || '').length}/5000`"></p>

                                        <button
                                            type="button"
                                            class="inline-flex h-8 items-center justify-center rounded-lg px-4 text-xs font-semibold text-white transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                                            :class="submitting
                                                ? 'cursor-not-allowed bg-slate-300'
                                                : 'bg-[#8a0b4e] hover:bg-[#6d0940]'"
                                            :disabled="submitting"
                                            x-on:click="
                                                if (!content.trim()) { showEmptyAlert = true; return; }
                                                showEmptyAlert = false;
                                                submit();
                                            "
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
                            <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->guest_name }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $bug->guest_email }}</p>
                        </div>

                        <div class="px-6 py-4">
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Versi Aplikasi</p>
                            <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->guest_version ?: '—' }}</p>
                        </div>

                        <div class="px-6 py-4">
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Dilaporkan Pada</p>
                            <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                        </div>
                    </div>
                </section>

                {{-- Prioritas --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                            Prioritas
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">Penetapan Prioritas</p>
                        <p class="mt-1 text-sm text-slate-500">
                            <template x-if="canEditPriority">
                                <span>Tiket masih berstatus Dilaporkan. Tetapkan prioritas sebelum melakukan penugasan.</span>
                            </template>
                            <template x-if="!canEditPriority">
                                <span>Prioritas dikunci karena tiket sudah dalam tahap pengerjaan.</span>
                            </template>
                        </p>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Prioritas Saat Ini</p>
                            <div class="mt-1.5 flex items-center gap-2">
                                <template x-if="priority">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide leading-none whitespace-nowrap"
                                        x-bind:style="priorityBadgeStyle(priority)"
                                        x-text="priority.level"
                                    ></span>
                                </template>

                                <template x-if="!priority">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                        Belum diprioritaskan
                                    </span>
                                </template>

                                <p class="text-xs text-slate-400" x-show="priority && priority.sla_hours">
                                    SLA <span class="font-medium text-slate-600" x-text="priority ? priority.sla_hours : ''"></span> jam
                                </p>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('pm.issues.priority.update', $bug) }}"
                            class="space-y-3"
                            @submit.prevent="submitPriority()"
                        >
                            @csrf

                            <div
                                x-data="{
                                    open: false,
                                    items: @js($priorities->map(fn($p) => [
                                        'value' => (string) $p->id,
                                        'label' => $p->level . ' (SLA ' . $p->sla_hours . ' jam)',
                                    ])->values()),
                                    get selectedLabel() {
                                        if (!this.priorityId) return 'Pilih Prioritas';
                                        const found = this.items.find(i => i.value === String(this.priorityId));
                                        return found ? found.label : 'Pilih Prioritas';
                                    },
                                }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                                class="relative"
                            >
                                <input type="hidden" name="priority_id" x-bind:value="priorityId" />

                                <button
                                    type="button"
                                    @click="if (canEditPriority && !prioritySubmitting) open = !open"
                                    class="inline-flex h-9 w-full items-center justify-between gap-2 rounded-lg border bg-white px-3 text-xs transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                                    :class="[
                                        (!canEditPriority || prioritySubmitting) ? 'cursor-not-allowed opacity-50 border-slate-200' : '',
                                        open ? 'border-[rgba(138,11,78,0.35)] ring-2 ring-[rgba(138,11,78,0.10)]' : 'border-slate-200 hover:border-[rgba(138,11,78,0.20)] hover:bg-[rgba(138,11,78,0.02)]',
                                    ]"
                                    :disabled="!canEditPriority || prioritySubmitting"
                                >
                                    <span
                                        class="truncate"
                                        :class="priorityId ? 'font-medium text-slate-800' : 'text-slate-400'"
                                        x-text="selectedLabel"
                                    ></span>

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2"
                                         class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-150"
                                         :class="open ? 'rotate-180 text-[#8a0b4e]' : ''" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition:enter="transition duration-150 ease-out"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition duration-100 ease-in"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="absolute left-0 top-11 z-50 w-full overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-lg shadow-slate-900/[0.06]"
                                >
                                    <div class="max-h-52 overflow-y-auto">
                                        <template x-for="item in items" :key="item.value">
                                            <button
                                                type="button"
                                                @click="priorityId = item.value; open = false"
                                                class="flex w-full items-center rounded-lg px-2.5 py-2 text-left text-xs transition-colors duration-100"
                                                :class="String(priorityId) === item.value
                                                    ? 'bg-[rgba(138,11,78,0.06)] font-semibold text-[#8a0b4e]'
                                                    : 'text-slate-700 hover:bg-[rgba(138,11,78,0.04)] hover:text-[#8a0b4e]'"
                                            >
                                                <span class="truncate" x-text="item.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                                :disabled="!canEditPriority || !priorityId || prioritySubmitting"
                                :class="(!canEditPriority || !priorityId || prioritySubmitting)
                                    ? 'cursor-not-allowed bg-slate-200 text-slate-400'
                                    : 'bg-[#8a0b4e] text-white hover:bg-[#6d0940]'"
                            >
                                <span x-text="priority ? 'Perbarui Prioritas' : 'Tetapkan Prioritas'"></span>
                            </button>

                            <p class="text-xs text-slate-400" x-show="!canEditPriority" style="display:none">
                                Prioritas dikunci — tiket sudah berstatus
                                <span class="font-medium text-slate-600" x-text="statusLabelUi(bug.status)"></span>.
                            </p>
                        </form>
                    </div>
                </section>

                {{-- Penugasan --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                            Penugasan
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">Programmer</p>
                        <p class="mt-1 text-sm text-slate-500">
                            <template x-if="bug.status === 'Reported'">
                                <span>Pilih programmer untuk memulai penugasan. Pastikan prioritas sudah ditetapkan terlebih dahulu.</span>
                            </template>
                            <template x-if="bug.status === 'Assigned'">
                                <span>Tiket sudah ditugaskan. Kamu masih bisa memindahkan ke programmer lain jika diperlukan.</span>
                            </template>
                            <template x-if="!['Reported', 'Assigned'].includes(bug.status)">
                                <span>Penugasan dikunci karena tiket sudah dalam tahap pengerjaan.</span>
                            </template>
                        </p>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <div>
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Penanggung Jawab</p>
                            <p class="mt-1.5 text-sm font-medium text-slate-800" x-text="assigneeName"></p>
                        </div>

                        <form
                            id="pm-assign-form"
                            method="POST"
                            action="{{ route('pm.issues.assign', $bug) }}"
                            class="space-y-3"
                            @submit.prevent="openAssignConfirm()"
                            x-show="canAssign"
                        >
                            @csrf

                            <p
                                class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700"
                                x-show="bug.status === 'Reported' && !priority"
                                style="display:none"
                            >
                                Tetapkan prioritas terlebih dahulu sebelum menugaskan programmer.
                            </p>

                            <div
                                x-data="{
                                    open: false,
                                    q: '',
                                    items: @js($programmers->map(fn($d) => [
                                        'value'   => (string) $d->id,
                                        'label'   => $d->name,
                                        'initial' => strtoupper(mb_substr($d->name, 0, 1)),
                                    ])->values()),
                                    get filtered() {
                                        const term = (this.q || '').toLowerCase().trim();
                                        if (!term) return this.items;
                                        return this.items.filter(i => (i.label || '').toLowerCase().includes(term));
                                    },
                                    get selectedLabel() {
                                        if (!this.assigneeId) return 'Pilih Programmer';
                                        const found = this.items.find(i => i.value === String(this.assigneeId));
                                        return found ? found.label : 'Pilih Programmer';
                                    },
                                    pick(item) {
                                        this.assigneeId = item.value;
                                        this.assigneeNameSelected = item.label;
                                        this.open = false;
                                        this.q = '';
                                    },
                                }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                                class="relative"
                            >
                                <input type="hidden" name="assignee_id" x-bind:value="assigneeId" />

                                <button
                                    type="button"
                                    @click="if (canAssign) open = !open"
                                    class="inline-flex h-9 w-full items-center justify-between gap-2 rounded-lg border bg-white px-2.5 text-xs transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                                    :class="open
                                        ? 'border-[rgba(138,11,78,0.35)] ring-2 ring-[rgba(138,11,78,0.10)]'
                                        : 'border-slate-200 hover:border-[rgba(138,11,78,0.20)] hover:bg-[rgba(138,11,78,0.02)]'"
                                >
                                    <span
                                        class="truncate"
                                        :class="assigneeId ? 'font-medium text-slate-700' : 'text-slate-400'"
                                        x-text="selectedLabel"
                                    ></span>

                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2"
                                         class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-150"
                                         :class="open ? 'rotate-180 text-[#8a0b4e]' : ''" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition:enter="transition duration-150 ease-out"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition duration-100 ease-in"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="absolute left-0 top-11 z-50 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg shadow-slate-900/[0.06]"
                                >
                                    <template x-if="items.length > 5">
                                        <div class="border-b border-slate-100 p-1.5">
                                            <input
                                                type="text"
                                                x-model="q"
                                                placeholder="Cari programmer…"
                                                class="h-8 w-full rounded-lg border border-slate-200 bg-white px-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[rgba(138,11,78,0.35)] focus:outline-none focus:ring-2 focus:ring-[rgba(138,11,78,0.10)]"
                                            />
                                        </div>
                                    </template>

                                    <div class="max-h-52 overflow-y-auto p-1">
                                        <template x-for="item in filtered" :key="item.value">
                                            <button
                                                type="button"
                                                @click="pick(item)"
                                                class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-xs transition-colors duration-100"
                                                :class="String(assigneeId) === item.value
                                                    ? 'bg-[rgba(138,11,78,0.06)] font-semibold text-[#8a0b4e]'
                                                    : 'text-slate-700 hover:bg-[rgba(138,11,78,0.04)] hover:text-[#8a0b4e]'"
                                            >
                                                <span
                                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-bold uppercase"
                                                    :class="String(assigneeId) === item.value
                                                        ? 'bg-[rgba(138,11,78,0.10)] text-[#8a0b4e]'
                                                        : 'bg-slate-100 text-slate-500'"
                                                    x-text="item.initial"
                                                ></span>
                                                <span class="truncate" x-text="item.label"></span>
                                            </button>
                                        </template>

                                        <div x-show="filtered.length === 0"
                                             class="px-2.5 py-3 text-center text-xs text-slate-400">
                                            Programmer tidak ditemukan
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <template x-if="bug.status === 'Assigned' && assigneeName !== '—'">
                                <p class="text-xs text-slate-400">
                                    Tiket akan dipindahkan ke programmer yang dipilih.
                                </p>
                            </template>

                            <button
                                type="submit"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg text-xs font-semibold transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                                :disabled="!assigneeId || submitting || (bug.status === 'Reported' && !priority)"
                                :class="(!assigneeId || submitting || (bug.status === 'Reported' && !priority))
                                    ? 'cursor-not-allowed bg-slate-200 text-slate-400'
                                    : 'bg-[#8a0b4e] text-white hover:bg-[#6d0940]'"
                            >
                                <span x-text="(bug.status === 'Assigned' && assigneeName !== '—') ? 'Ganti Programmer' : 'Tugaskan'"></span>
                            </button>
                        </form>

                        <p class="text-xs text-slate-400" x-show="!canAssign" style="display:none">
                            Penugasan dikunci — tiket sudah berstatus
                            <span class="font-medium text-slate-600" x-text="statusLabelUi(bug.status)"></span>.
                        </p>

                        <form
                            id="pm-unassign-form"
                            method="POST"
                            action="{{ route('pm.issues.unassign', $bug) }}"
                            class="border-t border-slate-100 pt-4"
                            @submit.prevent="openUnassignConfirm()"
                            x-show="bug.status === 'Assigned'"
                            style="display:none"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="text-xs font-medium text-rose-500 transition-colors hover:text-rose-700"
                            >
                                Batalkan Penugasan
                            </button>
                        </form>
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

        {{-- Modal: Konfirmasi Penugasan --}}
        <x-pm.modal-confirm
            name="pm-confirm-assignment"
            :show="false"
            maxWidth="md"
            x-data="{ assigneeName: '', actionLabel: 'Tugaskan', ticket: '', bugTitle: '', submitting: false }"
            x-on:pm-open-assignment-confirm.window="
                assigneeName = $event.detail?.assigneeName || '';
                actionLabel  = $event.detail?.actionLabel  || 'Tugaskan';
                ticket       = $event.detail?.ticket       || '';
                bugTitle     = $event.detail?.bugTitle     || '';
                submitting   = false;
                $dispatch('open-modal', 'pm-confirm-assignment');
            "
            x-on:pm-assignment-finished.window="submitting = false"
        >
            <x-slot:title>
                <span x-text="actionLabel === 'Ganti programmer' ? 'Konfirmasi Pergantian Programmer' : 'Konfirmasi Penugasan'"></span>
            </x-slot:title>

            <x-slot:description>
                <span class="block text-sm text-slate-500">
                    <span class="font-medium text-slate-800" x-text="ticket"></span>
                    <span class="text-slate-300">·</span>
                    <span x-text="bugTitle"></span>
                </span>
                <span class="mt-2 block text-sm text-slate-500">
                    Programmer tujuan: <span class="font-medium text-slate-800" x-text="assigneeName || '—'"></span>
                </span>
                <span class="mt-2 block text-sm text-slate-500">Lanjutkan proses ini?</span>
            </x-slot:description>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    type="button"
                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-colors hover:border-[rgba(138,11,78,0.20)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
                    x-on:click="$dispatch('close-modal', 'pm-confirm-assignment')"
                    x-bind:disabled="submitting"
                >
                    Batal
                </button>
                <button
                    type="button"
                    class="inline-flex h-8 items-center justify-center rounded-lg px-4 text-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                    :class="submitting
                        ? 'cursor-not-allowed bg-slate-200 text-slate-400'
                        : 'bg-[#8a0b4e] text-white hover:bg-[#6d0940]'"
                    x-bind:disabled="submitting"
                    x-on:click="
                        submitting = true;
                        window.dispatchEvent(new CustomEvent('pm-confirm-assign'));
                    "
                >
                    <span x-show="!submitting">Ya, Lanjutkan</span>
                    <span x-show="submitting" x-cloak>Memproses…</span>
                </button>
            </div>
        </x-pm.modal-confirm>

        {{-- Modal: Batalkan Penugasan --}}
        <x-pm.modal-confirm
            name="pm-confirm-unassign"
            :show="false"
            maxWidth="md"
            variant="danger"
            x-data="{ ticket: '', bugTitle: '', assigneeName: '', submitting: false }"
            x-on:pm-open-unassign-confirm.window="
                ticket       = $event.detail?.ticket       || '';
                bugTitle     = $event.detail?.bugTitle     || '';
                assigneeName = $event.detail?.assigneeName || '';
                submitting   = false;
                $dispatch('open-modal', 'pm-confirm-unassign');
            "
            x-on:pm-unassign-finished.window="submitting = false"
        >
            <x-slot:title>Batalkan Penugasan</x-slot:title>

            <x-slot:description>
                <span class="block text-sm text-slate-500">
                    <span class="font-medium text-slate-800" x-text="ticket"></span>
                    <span class="text-slate-300">·</span>
                    <span x-text="bugTitle"></span>
                </span>
                <span class="mt-2 block text-sm text-slate-500">
                    {{ __('labels.current_assignee') }}:
                    <span class="font-medium text-slate-800" x-text="assigneeName || '—'"></span>
                </span>
                <span class="mt-2 block text-sm text-slate-500">
                    Penugasan akan dibatalkan dan status dikembalikan ke
                    <span class="font-medium text-slate-800">Dilaporkan</span>.
                </span>
            </x-slot:description>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    type="button"
                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-colors hover:border-[rgba(138,11,78,0.20)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
                    x-on:click="$dispatch('close-modal', 'pm-confirm-unassign')"
                    x-bind:disabled="submitting"
                >
                    Batal
                </button>
                <button
                    type="button"
                    class="inline-flex h-8 items-center justify-center rounded-lg px-4 text-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(190,18,60,0.30)] focus-visible:ring-offset-1"
                    :class="submitting
                        ? 'cursor-not-allowed bg-slate-200 text-slate-400'
                        : 'bg-rose-600 text-white hover:bg-rose-700'"
                    x-bind:disabled="submitting"
                    x-on:click="
                        submitting = true;
                        window.dispatchEvent(new CustomEvent('pm-confirm-unassign'));
                    "
                >
                    <span x-show="!submitting">Ya, Batalkan</span>
                    <span x-show="submitting" x-cloak>Memproses…</span>
                </button>
            </div>
        </x-pm.modal-confirm>

    </div>
@endsection