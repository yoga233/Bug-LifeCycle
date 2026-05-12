@extends('layouts.programmer')

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

        $backUrl = $isSafeReturn ? urldecode($rawReturn) : route('programmer.dashboard');

        $returnLabel = 'Dashboard';
        if ($backUrl) {
            if (str_contains($backUrl, 'kinerja')) {
                $returnLabel = 'Riwayat Kinerja';
            } elseif (str_contains($backUrl, 'notifications')) {
                $returnLabel = 'Notifikasi';
            } elseif (str_contains($backUrl, 'dashboard')) {
                $returnLabel = 'Dashboard';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Split deskripsi + langkah reproduksi
        |--------------------------------------------------------------------------
        */
        $rawDescription   = (string) ($bug->description ?? '');
        $marker           = 'Langkah Reproduksi:';
        $descriptionText  = $rawDescription;
        $reproductionSteps = '';

        if (str_contains($rawDescription, $marker)) {
            [$descPart, $reproPart] = array_pad(explode($marker, $rawDescription, 2), 2, '');
            $descriptionText   = trim($descPart);
            $reproductionSteps = trim($reproPart);
        }

        /*
        |--------------------------------------------------------------------------
        | Parse SLA info from title
        |--------------------------------------------------------------------------
        */
        $rawTitle         = (string) ($bug->title ?? '');
        $titleDisplay     = $rawTitle;
        $titleSuffix      = '';
        $titleSuffixClass = 'text-slate-400';

        if (preg_match('/\s*-\s*(SLA\s+(?:Terlambat|Tepat|Terlewat)[^-]*?)(?:\s*-\s*|$)/iu', $rawTitle, $m)) {
            $titleSuffix  = trim(preg_replace('/^SLA\s+/iu', '', (string) $m[1]));
            $titleDisplay = trim(str_replace($m[0], ' - ', $rawTitle), ' -');
            $titleDisplay = trim(preg_replace('/\s*-\s*-\s*/', ' - ', $titleDisplay), ' -');

            $sl = mb_strtolower($titleSuffix);
            if (str_contains($sl, 'terlambat') || str_contains($sl, 'terlewat')) {
                $titleSuffixClass = 'text-amber-600';
            } elseif (str_contains($sl, 'tepat')) {
                $titleSuffixClass = 'text-emerald-600';
            }
        }

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
                    <span>{{ $titleDisplay }}</span>
                    @if ($titleSuffix !== '')
                        <span class="ml-1.5 text-sm font-medium {{ $titleSuffixClass }}">
                            — {{ $titleSuffix }}
                        </span>
                    @endif
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-500">
                    Tinjau laporan, perbarui progres pengerjaan, dan simpan catatan teknis di komentar agar tindak lanjut lebih jelas.
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
        class="grid grid-cols-1 gap-6 lg:grid-cols-3"
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
                        Informasi utama yang dibutuhkan untuk memahami masalah dan melakukan perbaikan.
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
                                <p class="mt-1 text-sm font-semibold text-slate-800">Instruksi Perbaikan QA</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-rose-700">
                                {{ $rejectionComments->count() }} Siklus Penolakan
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            Daftar penolakan dari QA. Selesaikan poin-poin di bawah ini sebelum mengirim kembali untuk pengujian.
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
                        Catat progres, kendala teknis, dan keputusan perbaikan agar riwayat kerja tetap jelas.
                    </p>
                </div>

                <div class="px-6 py-5">
                    <div
                        x-data="{
                            ...pmCommentSection({
                                postUrl: '{{ route('programmer.bugs.comments.store', $bug) }}',
                                csrf: '{{ csrf_token() }}',
                                initialComments: {{ $bug->comments->where('type', 'discussion')->map(fn($c) => [
                                    'id' => $c->id,
                                    'content' => $c->content,
                                    'user_name' => $c->user?->name,
                                    'user_initial' => strtoupper(substr($c->user?->name ?? 'U', 0, 1)),
                                    'created_at' => $c->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'),
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
                                action="{{ route('programmer.bugs.comments.store', $bug) }}"
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
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                                    placeholder="Tulis update progres, kendala teknis, atau catatan perbaikan…"
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
                                        class="inline-flex h-8 items-center justify-center rounded-lg bg-[#8a0b4e] px-4 text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] disabled:cursor-not-allowed disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
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
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Penanggung Jawab</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->assignee?->name ?? '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Dilaporkan Pada</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-800">{{ $bug->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- Aksi Programmer --}}
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Tindakan
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Aksi Programmer</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($bug->status === 'Assigned')
                            Mulai pengerjaan untuk mengubah status tiket menjadi Dalam Pengerjaan.
                        @elseif ($bug->status === 'In Progress')
                            Setelah perbaikan selesai, kirim tiket ke tahap Pengujian.
                        @elseif ($bug->status === 'Testing')
                            Tiket sedang menunggu validasi dari QA.
                        @elseif ($bug->status === 'Rejected')
                            QA mengembalikan tiket ini. Periksa catatan QA, lalu lanjutkan perbaikan.
                        @else
                            Tidak ada aksi yang tersedia pada status ini.
                        @endif
                    </p>
                </div>

                <div class="px-6 py-5">
                    <div x-show="status === 'Assigned'">
                        <form @submit.prevent="postJson('{{ route('programmer.bugs.start', $bug) }}')">
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg bg-[#8a0b4e] text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="!submitting">Mulai Pengerjaan</span>
                                <span x-show="submitting" x-cloak>Memproses...</span>
                            </button>
                        </form>
                    </div>

                    <div x-show="status === 'In Progress'">
                        <form @submit.prevent="postJson('{{ route('programmer.bugs.sendToTesting', $bug) }}')">
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg bg-[#8a0b4e] text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="!submitting">Kirim ke Pengujian</span>
                                <span x-show="submitting" x-cloak>Memproses...</span>
                            </button>
                        </form>
                    </div>

                    <div x-show="status === 'Rejected'">
                        <form @submit.prevent="postJson('{{ route('programmer.bugs.start', $bug) }}')">
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="inline-flex h-8 w-full items-center justify-center rounded-lg bg-[#8a0b4e] text-xs font-semibold text-white transition-all duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="!submitting">Kerjakan Ulang</span>
                                <span x-show="submitting" x-cloak>Memproses...</span>
                            </button>
                        </form>
                    </div>

                    <div x-show="status === 'Testing'">
                        <p class="text-sm text-slate-500">
                            Tiket sedang diverifikasi oleh QA. Tidak ada aksi yang diperlukan saat ini.
                        </p>
                    </div>

                    <div x-show="!['Assigned', 'In Progress', 'Rejected', 'Testing'].includes(status)">
                        <p class="text-sm text-slate-500">
                            Tidak ada aksi yang tersedia pada status ini.
                        </p>
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
                        Ditetapkan oleh Project Manager sebagai acuan pengerjaan.
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

@endsection