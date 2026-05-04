@extends('layouts.qa')

@section('title', 'QA - Bug Detail')

@section('content')
    @php
        $currentUrl = url()->current();
        $rawReturn = (string) request()->query('return', '');

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $isSafeReturn = false;

        if ($rawReturn !== '') {
            $returnHost = parse_url($rawReturn, PHP_URL_HOST);
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

        $backUrl = $isSafeReturn ? $rawReturn : route('bugs.testing-queue');
        if ($backUrl === $currentUrl) {
            $backUrl = route('bugs.testing-queue');
        }

        $rawDescription = (string) ($bug->description ?? '');
        $marker = 'Langkah Reproduksi:';
        $descriptionText = $rawDescription;
        $reproductionSteps = '';

        if (str_contains($rawDescription, $marker)) {
            [$descPart, $reproPart] = array_pad(explode($marker, $rawDescription, 2), 2, '');
            $descriptionText = trim($descPart);
            $reproductionSteps = trim($reproPart);
        }

        $orderedHistories = ($bug->statusHistories ?? collect())->sortBy('changed_at')->values();

        $key = fn ($s) => str((string) $s)->lower()->replace(' ', '_')->toString();
        $label = fn ($s) => match ($s) {
            'reported'     => 'Dilaporkan',
            'assigned'     => 'Ditugaskan',
            'in_progress'  => 'Dalam Pengerjaan',
            'testing'      => 'Pengujian',
            'resolved'     => 'Diselesaikan',
            'closed'       => 'Ditutup',
            'rejected'     => 'Dikembalikan',
            default        => ucfirst(str_replace('_', ' ', (string) $s)),
        };

        $dot = fn ($s) => match ($s) {
            'reported'     => 'bg-slate-400',
            'assigned'     => 'bg-purple-500',
            'in_progress'  => 'bg-amber-500',
            'testing'      => 'bg-blue-500',
            'resolved'     => 'bg-emerald-500',
            'closed'       => 'bg-gray-500',
            'rejected'     => 'bg-red-500',
            default        => 'bg-slate-300',
        };

        $line = fn ($s) => match ($s) {
            'reported'     => 'bg-slate-200',
            'assigned'     => 'bg-purple-200',
            'in_progress'  => 'bg-amber-200',
            'testing'      => 'bg-blue-200',
            'resolved'     => 'bg-emerald-200',
            'closed'       => 'bg-gray-200',
            'rejected'     => 'bg-red-200',
            default        => 'bg-slate-200',
        };

        $timeline = collect();
        $initialStatus = $orderedHistories->first()?->old_status ?? $bug->status;

        $timeline->push([
            'status' => $initialStatus,
            'at'     => $bug->created_at,
            'note'   => 'Tiket dibuat',
        ]);

        foreach ($orderedHistories as $history) {
            $timeline->push([
                'status' => $history->new_status,
                'at'     => $history->changed_at,
                'note'   => 'Dari ' . $label($key($history->old_status)),
            ]);
        }

        if (($timeline->last()['status'] ?? null) !== $bug->status) {
            $timeline->push([
                'status' => $bug->status,
                'at'     => $bug->updated_at,
                'note'   => 'Status saat ini',
            ]);
        }

        $returnLabel = 'Antrian Pengujian';
        if ($backUrl) {
            if (str_contains($backUrl, 'dashboard')) {
                $returnLabel = 'Dashboard';
            } elseif (str_contains($backUrl, 'testing-queue')) {
                $returnLabel = 'Antrian Pengujian';
            }
        }
    @endphp

    <div class="mb-8">

        {{-- Button Kembali --}}
        <div class="mb-4">
            
                <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 transition-colors hover:text-[#8a0b4e]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                    class="h-3 w-3" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M9.78 4.22a.75.75 0 0 1 0 1.06L7.06 8l2.72 2.72a.75.75 0 1 1-1.06 1.06L5.47 8.53a.75.75 0 0 1 0-1.06l3.25-3.25a.75.75 0 0 1 1.06 0Z"
                        clip-rule="evenodd" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        {{-- Breadcrumb --}}
        <div class="mb-4 flex items-center gap-2 text-xs">
            <a href="{{ $backUrl }}" class="text-slate-400 transition-colors hover:text-[#8a0b4e]">
                {{ $returnLabel }}
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="h-3 w-3 text-slate-300" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium text-slate-600">Detail Bug</span>
        </div>

        {{-- Title & badges --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $ticket }} · {{ $bug->title }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                    <span class="text-slate-400">{{ $bug->project?->name ?? '—' }}</span>
                    @if($bug->severity)
                        <span class="text-slate-300">·</span>
                        <x-severity-badge :severity="$bug->severity" class="px-2 py-0.5 text-[11px] font-semibold" />
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-pm.status-badge :status="$bug->status" variant="pill" :dot="true" />
                @if($bug->priority)
                    <x-priority-badge :priority="$bug->priority" class="px-3 py-1.5 text-xs" />
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide leading-none whitespace-nowrap text-slate-500">
                        Belum diprioritaskan
                    </span>
                @endif
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Card Detail Laporan --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Laporan</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Detail Laporan</p>
                    <p class="mt-1 text-sm text-slate-500">Informasi konteks bug untuk proses verifikasi QA.</p>
                </div>

                <div class="space-y-6 px-6 py-5">

                    {{-- Deskripsi --}}
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Deskripsi</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $descriptionText }}</p>
                    </div>

                    {{-- Langkah Reproduksi --}}
                    @if($reproductionSteps !== '')
                        <div class="border-t border-slate-100 pt-5">
                            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Langkah Reproduksi</p>
                            <p class="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $reproductionSteps }}</p>
                        </div>
                    @endif

                    {{-- Lampiran --}}
                    <div class="border-t border-slate-100 pt-5">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Lampiran</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @forelse($bug->attachments as $file)
                                @php
                                    $fileName  = (string) ($file->file_name ?? 'file');
                                    $fileType  = strtolower((string) ($file->file_type ?? ''));
                                    $isImage   = str_starts_with($fileType, 'image/') || preg_match('/\.(png|jpe?g|gif|webp)$/i', $fileName);
                                    $publicUrl = isset($file->file_path) ? asset('storage/'.$file->file_path) : null;
                                @endphp
                                
                                    href="{{ $publicUrl ?? '#' }}"
                                    @if($publicUrl) target="_blank" rel="noopener" @endif
                                    class="group flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-3 transition-colors duration-200 hover:border-[#8a0b4e]/20 hover:bg-[#8a0b4e]/[0.01]"
                                    title="{{ $fileName }}"
                                >
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                        @if($isImage && $publicUrl)
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
                                            {{ $file->file_size ? $file->file_size.' KB' : '' }}
                                            @if(!empty($fileType))
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

            {{-- Card Komentar --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Diskusi</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Komentar</p>
                    <p class="mt-1 text-sm text-slate-500">Catatan kolaborasi PM, Programmer, dan QA pada bug ini.</p>
                </div>

                <div class="px-6 py-5">
                    <div class="space-y-4">
                        @forelse($bug->comments as $comment)
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
                                <div class="flex-1 min-w-0">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-medium text-slate-900">{{ $authorName }}</span>
                                        <span class="text-xs text-slate-400">{{ $comment->created_at?->format('d M Y, H:i') }}</span>
                                    </div>
                                    <p class="text-sm leading-relaxed text-slate-600 whitespace-pre-line">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center">
                                <p class="text-sm text-slate-400">Belum ada komentar pada bug ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Card QA Actions --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Tindakan</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Validasi QA</p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if($bug->status === 'Testing')
                            Pilih hasil validasi untuk melanjutkan lifecycle bug.
                        @else
                            Aksi QA hanya tersedia saat status bug berada di tahap Pengujian.
                        @endif
                    </p>
                </div>

                <div class="px-6 py-5">
                    @if($bug->status === 'Testing')
                        <div class="space-y-4">

                            {{-- Approve --}}
                            <form method="POST" action="{{ route('qa.bugs.approve', $bug) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex h-9 w-full items-center justify-center rounded-xl text-xs font-medium text-white transition-colors"
                                    style="background-color: #059669;"
                                    onmouseover="this.style.backgroundColor='#047857'"
                                    onmouseout="this.style.backgroundColor='#059669'"
                                >
                                    Approve — Tandai Selesai
                                </button>
                            </form>

                            {{-- Reject --}}
                            <form method="POST" action="{{ route('qa.bugs.reject', $bug) }}" class="space-y-3">
                                @csrf
                                <label class="block text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400" for="reason">
                                    Catatan Pengembalian
                                </label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="3"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-300 transition-colors duration-150 focus:border-[#8a0b4e] focus:outline-none focus:ring-2 focus:ring-[#f5e8ef]"
                                    placeholder="Contoh: langkah X masih error pada skenario tertentu…"
                                >{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                                <button
                                    type="submit"
                                    class="inline-flex h-9 w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-xs font-medium text-amber-700 transition-colors hover:bg-amber-100"
                                >
                                    Kembalikan ke Programmer
                                </button>
                            </form>

                        </div>
                    @else
                        <p class="text-sm text-slate-400">Tidak ada aksi yang dapat dilakukan pada status ini.</p>
                    @endif
                </div>
            </section>

            {{-- Card Ringkasan --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Info</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Ringkasan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-6 py-4">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Reporter</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->guest_name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $bug->guest_email ?? '—' }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">{{ __('labels.assignee') }}</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->assignee?->name ?? '—' }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Versi Aplikasi</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->guest_version ?? '—' }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Dilaporkan Pada</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Terakhir Diperbarui</p>
                        <p class="mt-1.5 text-sm font-medium text-slate-900">{{ $bug->updated_at?->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- Card Status Timeline --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">Riwayat</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">Status Timeline</p>
                </div>

                <div class="px-6 py-5">
                    @forelse($timeline as $item)
                        @php($statusKey = $key($item['status']))
                        <div class="flex gap-3">

                            {{-- Dot & line --}}
                            <div class="flex flex-col items-center">
                                <div class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $dot($statusKey) }}"></div>
                                @unless($loop->last)
                                    <div class="mt-1 w-px flex-1 {{ $line($statusKey) }}" style="min-height:24px"></div>
                                @endunless
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1 {{ $loop->last ? 'pb-0' : 'pb-4' }}">
                                <p class="text-sm font-medium text-slate-900">{{ $label($statusKey) }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $item['at']?->format('d M Y, H:i') ?? '—' }}
                                </p>
                                @if(!empty($item['note']))
                                    <p class="mt-0.5 text-xs text-slate-400 italic">{{ $item['note'] }}</p>
                                @endif
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