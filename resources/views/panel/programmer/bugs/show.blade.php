@extends('layouts.programmer')

@section('title', 'Detail Bug')

@section('content')
    @php
        $currentUrl = url()->current();
        $rawReturn  = (string) request()->query('return', '');
        $appHost    = parse_url(config('app.url'), PHP_URL_HOST);

        $isSafeReturn = false;
        if ($rawReturn !== '') {
            $returnHost   = parse_url($rawReturn, PHP_URL_HOST);
            $returnScheme = parse_url($rawReturn, PHP_URL_SCHEME);

            if ($returnScheme === null && $returnHost === null && str_starts_with($rawReturn, '/')) {
                $isSafeReturn = true;
            }
            if (! $isSafeReturn && $returnHost && $appHost && $returnHost === $appHost) {
                $isSafeReturn = true;
            }
            if ($isSafeReturn && $rawReturn === $currentUrl) {
                $isSafeReturn = false;
            }
        }

        $backUrl = $isSafeReturn ? $rawReturn : url()->previous();
        if ($backUrl === $currentUrl) {
            $backUrl = route('programmer.dashboard');
        }

        // Breadcrumb label
        $returnLabel = 'Dashboard';
        if ($backUrl) {
            if (str_contains($backUrl, 'kinerja')) {
                $returnLabel = 'Riwayat Kinerja';
            } elseif (str_contains($backUrl, 'dashboard')) {
                $returnLabel = 'Dashboard';
            } elseif (str_contains($backUrl, 'bugs')) {
                $returnLabel = 'Daftar Bug';
            }
        }

        // Description split
        $rawDescription = (string) ($bug->description ?? '');
        $marker         = 'Langkah Reproduksi:';
        $descriptionText    = $rawDescription;
        $reproductionSteps  = '';

        if (str_contains($rawDescription, $marker)) {
            [$descPart, $reproPart] = array_pad(explode($marker, $rawDescription, 2), 2, '');
            $descriptionText   = trim($descPart);
            $reproductionSteps = trim($reproPart);
        }

        // Parse SLA from title
        $rawTitle     = (string) ($bug->title ?? '');
        $titleDisplay = $rawTitle;
        $titleSuffix  = '';
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

        $ticketLabel = $ticket ?? ($bug->ticket ?? sprintf('#BUG-%06d', $bug->id));

        // Timeline helpers
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
            $events->push(['status' => $h->new_status, 'at' => $h->changed_at]);
        }

        if (($events->last()['status'] ?? null) !== $bug->status) {
            $events->push(['status' => $bug->status, 'at' => $bug->updated_at]);
        }

        $events = $events->unique('status')->values();
    @endphp

    {{-- ============================================================
         Page Header
         ============================================================ --}}
    <div class="mb-8">

        {{-- Breadcrumb: 1 level dinamis --}}
        <nav class="mb-4 flex items-center gap-1.5" aria-label="Breadcrumb">
            <a
                href="{{ $backUrl ?: route('programmer.dashboard') }}"
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

            <span class="text-xs font-medium text-slate-600" aria-current="page">Detail Bug</span>
        </nav>

        {{-- Title & Badges --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            {{-- Left --}}
            <div class="min-w-0">
                <div class="mb-1.5 flex flex-wrap items-center gap-1.5">
                    <span class="font-mono text-[10px] font-semibold tracking-[0.06em] text-slate-400">
                        {{ $ticketLabel }}
                    </span>

                    @if ($bug->project?->name)
                        <span class="inline-flex items-center rounded-full border border-slate-200/80 bg-slate-50/80 px-2 py-0.5 text-[10px] font-medium text-slate-400">
                            {{ $bug->project->name }}
                        </span>
                    @endif

                    @if ($bug->severity)
                        <x-severity-badge :severity="$bug->severity" class="px-2 py-0.5 text-[10px] font-semibold" />
                    @endif
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-[28px]">
                    <span>{{ $titleDisplay }}</span>
                    @if ($titleSuffix !== '')
                        <span class="ml-1.5 text-sm font-medium {{ $titleSuffixClass }}">
                            — {{ $titleSuffix }}
                        </span>
                    @endif
                </h1>

                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Tinjau laporan, update progres, dan catat catatan perbaikan di komentar.
                </p>
            </div>

            {{-- Right --}}
            <div class="flex flex-wrap items-center gap-2">
                <x-pm.status-badge :status="$bug->status" variant="pill" :dot="true" />

                @if ($bug->priority)
                    <x-priority-badge :priority="$bug->priority" class="px-2.5 py-1 text-[11px]" />
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide leading-none whitespace-nowrap text-slate-500">
                        Belum diprioritaskan
                    </span>
                @endif
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
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Laporan</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Detail Laporan</p>
                    <p class="mt-1 text-sm text-slate-500">Informasi yang dibutuhkan untuk analisa dan implementasi perbaikan.</p>
                </div>

                <div class="space-y-6 px-6 py-5">

                    {{-- Deskripsi --}}
                    <div>
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Deskripsi</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $descriptionText }}</p>
                    </div>

                    {{-- Langkah Reproduksi --}}
                    @if ($reproductionSteps !== '')
                        <div class="border-t border-slate-100 pt-5">
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Langkah Reproduksi</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $reproductionSteps }}</p>
                        </div>
                    @endif

                    {{-- Lampiran --}}
                    <div class="border-t border-slate-100 pt-5">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Lampiran</p>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @forelse ($bug->attachments as $file)
                                @php
                                    $fileName  = (string) ($file->file_name ?? 'file');
                                    $fileType  = strtolower((string) ($file->file_type ?? ''));
                                    $isImage   = str_starts_with($fileType, 'image/') || preg_match('/\.(png|jpe?g|gif|webp)$/i', $fileName);
                                    $publicUrl = isset($file->file_path) ? asset('storage/' . $file->file_path) : null;
                                @endphp

                                <a
                                    href="{{ $publicUrl ?? '#' }}"
                                    @if ($publicUrl) target="_blank" rel="noopener" @endif
                                    class="group flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-3 transition-colors duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#8a0b4e]/[0.01]"
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

            {{-- Komentar --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Diskusi</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Komentar</p>
                    <p class="mt-1 text-sm text-slate-500">Diskusi teknis dan catatan progres perbaikan.</p>
                </div>

                <div class="px-6 py-5">
                    <div
                        x-data="{
                            ...pmCommentSection({
                                postUrl: '{{ route('programmer.bugs.comments.store', $bug) }}',
                                csrf: '{{ csrf_token() }}',
                                initialComments: {{ $bug->comments->map(fn($c) => [
                                    'id' => $c->id,
                                    'content' => $c->content,
                                    'user_name' => $c->user?->name,
                                    'user_initial' => strtoupper(substr($c->user?->name ?? 'U', 0, 1)),
                                    'created_at' => $c->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'),
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
                                        Tulis yang pertama.
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
                                        <span class="text-sm font-medium text-slate-900" x-text="g.user_name"></span>
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
                                    :disabled="submitting"
                                    x-on:input="if (content.trim()) showEmptyAlert = false"
                                ></textarea>

                                <p class="text-xs text-rose-500"
                                   x-show="showEmptyAlert" x-transition.opacity x-cloak>
                                    Kolom komentar wajib diisi sebelum mengirim.
                                </p>

                                <p class="text-xs text-red-600" x-show="error" x-text="error"></p>

                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] text-slate-400" x-text="`${(content || '').length}/5000`"></p>

                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-xl px-5 text-xs font-medium text-white transition-colors"
                                        :style="submitting ? 'background-color: #cbd5e1; cursor: not-allowed;' : 'background-color: #8a0b4e;'"
                                        :disabled="submitting"
                                        x-on:click="
                                            if (!content.trim()) { showEmptyAlert = true; return; }
                                            showEmptyAlert = false;
                                            submit();
                                        "
                                    >
                                        <span x-show="!submitting">Kirim Komentar</span>
                                        <span x-show="submitting">Mengirim...</span>
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
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Info</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Ringkasan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Reporter</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->guest_name }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $bug->guest_email }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Versi</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->guest_version ?: '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">{{ __('labels.assignee') }}</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->assignee?->name ?? '—' }}</p>
                    </div>

                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Dilaporkan Pada</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- Aksi Programmer --}}
            <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Tindakan</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Aksi Programmer</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($bug->status === 'Assigned')
                            Mulai pengerjaan untuk mengubah status menjadi Dalam Pengerjaan.
                        @elseif ($bug->status === 'In Progress')
                            Setelah perbaikan selesai, kirim bug ke tahap Pengujian.
                        @elseif ($bug->status === 'Testing')
                            Bug sedang menunggu validasi dari QA.
                        @else
                            Tidak ada aksi yang tersedia pada status ini.
                        @endif
                    </p>
                </div>

                <div class="px-6 py-5">
                    @if ($bug->status === 'Assigned')
                        <form method="POST" action="{{ route('programmer.bugs.start', $bug) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex h-9 w-full items-center justify-center rounded-xl bg-[#8a0b4e] text-xs font-medium text-white transition-colors hover:bg-[#6d0940]"
                            >
                                Mulai Pengerjaan
                            </button>
                        </form>
                    @elseif ($bug->status === 'In Progress')
                        <form method="POST" action="{{ route('programmer.bugs.sendToTesting', $bug) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex h-9 w-full items-center justify-center rounded-xl bg-[#8a0b4e] text-xs font-medium text-white transition-colors hover:bg-[#6d0940]"
                            >
                                Kirim ke Pengujian
                            </button>
                        </form>
                    @elseif ($bug->status === 'Testing')
                        <p class="text-sm text-slate-400">Bug sedang diverifikasi oleh QA. Tidak ada aksi yang diperlukan saat ini.</p>
                    @else
                        <p class="text-sm text-slate-400">Tidak ada aksi yang dapat dilakukan pada status ini.</p>
                    @endif
                </div>
            </section>

            {{-- Prioritas & SLA --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Prioritas</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Prioritas & SLA</p>
                    <p class="mt-1 text-sm text-slate-500">Ditetapkan oleh Project Manager sebagai acuan pengerjaan.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-6 py-4">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Prioritas Saat Ini</p>
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
                            <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Target SLA</p>
                            <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->priority->sla_hours }} jam</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Timeline --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400">Riwayat</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Status Timeline</p>
                </div>

                <div class="px-6 py-5">
                    @forelse ($events as $e)
                        @php($statusKey = $timelineKey($e['status']))
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $timelineDot($statusKey) }}"></div>
                                @unless ($loop->last)
                                    <div class="mt-1 w-px flex-1 {{ $timelineLine($statusKey) }}" style="min-height:24px"></div>
                                @endunless
                            </div>

                            <div class="min-w-0 flex-1 {{ $loop->last ? 'pb-0' : 'pb-4' }}">
                                <p class="text-sm font-medium text-slate-900">{{ $timelineLabel($statusKey) }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $e['at']?->format('d M Y, H:i') ?? '—' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada histori.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

@endsection