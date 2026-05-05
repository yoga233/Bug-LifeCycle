@extends('layouts.qa')

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
        $ticketLabel = $bug->ticket ?? sprintf('BUG-%06d', $bug->id);

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
            ]);
        }

        if (($events->last()['status'] ?? null) !== $bug->status) {
            $events->push([
                'status' => $bug->status,
                'at'     => $bug->updated_at,
            ]);
        }

        $events = $events->unique('status')->values();
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
                    {{ $bug->title }}
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">
                    Periksa hasil perbaikan programmer, lalu setujui atau kembalikan tiket ini dengan catatan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-pm.status-badge :status="$bug->status" variant="pill" :dot="true" />

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

            {{-- Komentar --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Diskusi
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Komentar</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Catatan kolaborasi antara PM, Programmer, dan QA pada tiket ini.
                    </p>
                </div>

                <div class="px-6 py-5">
                    <div class="space-y-4">
                        @forelse ($bug->comments as $comment)
                            @php
                                $authorName    = (string) ($comment->user?->name ?? 'System');
                                $authorInitial = strtoupper(substr($authorName, 0, 1));
                            @endphp

                            <div class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white"
                                    style="background-color: #8a0b4e;"
                                >
                                    {{ $authorInitial }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-medium text-slate-800">{{ $authorName }}</span>
                                        <span class="text-xs text-slate-400">{{ $comment->created_at?->format('d M Y, H:i') }}</span>
                                    </div>
                                    <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600">
                                        {{ $comment->content }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center">
                                <p class="text-sm text-slate-400">
                                    Belum ada komentar pada tiket ini.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Validasi QA --}}
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Tindakan
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Keputusan Validasi</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($bug->status === 'Testing')
                            Periksa hasil perbaikan, lalu pilih hasil validasi. Setujui jika sudah sesuai, atau kembalikan ke programmer jika masih ada masalah.
                        @else
                            Tindakan validasi hanya tersedia saat tiket berada pada status Pengujian.
                        @endif
                    </p>
                </div>

                <div class="px-6 py-5">
                    @if ($bug->status === 'Testing')
                        <div class="space-y-4">

                            {{-- Primary --}}
                            <form method="POST" action="{{ route('qa.bugs.approve', $bug) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex h-8 w-full items-center justify-center rounded-lg bg-[#8a0b4e] text-xs font-semibold text-white transition-colors duration-150 hover:bg-[#6d0940] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.30)] focus-visible:ring-offset-1"
                                >
                                    Setujui dan Tandai Selesai
                                </button>
                            </form>

                            {{-- Secondary --}}
                            <form method="POST" action="{{ route('qa.bugs.reject', $bug) }}" class="space-y-3">
                                @csrf

                                <label
                                    class="block font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-slate-400"
                                    for="reason"
                                >
                                    Catatan untuk Programmer
                                </label>

                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                                    placeholder="Jelaskan bagian yang masih bermasalah atau skenario yang belum lolos pengujian…"
                                >{{ old('reason') }}</textarea>

                                @error('reason')
                                    <p class="text-xs text-rose-500">{{ $message }}</p>
                                @enderror

                                <button
                                    type="submit"
                                    class="inline-flex h-8 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-xs font-medium text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.25)] focus-visible:ring-offset-1"
                                >
                                    Kembalikan ke Programmer
                                </button>
                            </form>

                        </div>
                    @else
                        <p class="text-sm leading-relaxed text-slate-500">
                            Saat ini belum ada tindakan yang dapat dilakukan karena tiket tidak berada pada tahap Pengujian.
                        </p>
                    @endif
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
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="font-mono text-[10px] font-medium uppercase tracking-[0.13em] text-[#8a0b4e]/60">
                        Riwayat
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Status Timeline</p>
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
                                <p class="text-sm font-medium text-slate-800">{{ $timelineLabel($statusKey) }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $e['at']?->format('d M Y, H:i') ?? '—' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada riwayat perubahan status.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

@endsection