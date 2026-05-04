@extends('layouts.project-manager')

@section('title', 'Dashboard Project Manager')

@section('content')

{{-- ============================================================
     Header Halaman
     ============================================================ --}}
<div class="mb-10">
    <p class="mb-2 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-[#8a0b4e]/60">
        Dashboard
    </p>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-800">
        Tindak Lanjut Tiket
    </h1>
    <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-500">
        Pantau penugasan, SLA, dan progres penyelesaian dari satu halaman.
    </p>
</div>

{{-- ============================================================
     Kartu Metrik
     ============================================================ --}}
<div class="mb-10 grid grid-cols-2 gap-3 xl:grid-cols-4">

    @php
        $metrics = [
            [
                'label'       => 'Menunggu Penugasan',
                'value'       => $needsAssignmentCount,
                'description' => 'Belum ada programmer penanggung jawab',
                'variant'     => $assignmentVariant,
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />',
            ],
            [
                'label'       => 'Melewati SLA',
                'value'       => $overdueSlaCount,
                'description' => 'Sudah melewati batas waktu penanganan',
                'variant'     => $slaVariant,
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            ],
            [
                'label'       => 'Prioritas Kritis',
                'value'       => $criticalOpenCount,
                'description' => 'Tiket kritis yang masih terbuka',
                'variant'     => $criticalVariant,
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
            ],
            [
                'label'       => 'Dalam Penanganan',
                'value'       => $activeCount,
                'description' => 'Sedang dikerjakan programmer',
                'variant'     => 'neutral',
                'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
            ],
        ];
    @endphp

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
     Daftar Tiket Menunggu Penugasan
     ============================================================ --}}
<section aria-labelledby="assignment-heading" class="mx-auto max-w-5xl">

    <div class="mb-2.5">
        <p class="mb-1.5 font-mono text-[11px] font-medium uppercase tracking-[0.15em] text-[#8a0b4e]/60">
            Penugasan
        </p>

        <div class="flex items-center gap-1.5">
            <h2
                id="assignment-heading"
                class="text-base font-semibold tracking-tight text-slate-800"
            >
                Menunggu Penugasan
            </h2>

            <x-pm.tooltip-help aria-label="Panduan penugasan tiket">
                <p class="font-semibold text-slate-800">Cara menugaskan tiket</p>
                <p class="mt-1.5 leading-relaxed text-slate-500">
                    Tentukan prioritas terlebih dahulu, lalu pilih programmer
                    yang akan menjadi penanggung jawab tiket ini.
                </p>
            </x-pm.tooltip-help>
        </div>

        <div class="mt-1 flex items-end justify-between gap-4">
            <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                Tiket di bawah ini belum memiliki programmer penanggung jawab.
                Tentukan prioritas, lalu pilih programmer yang akan menangani.
            </p>

            <a
                href="{{ route('pm.issues.index') }}"
                class="group inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-[#8a0b4e]/60 transition-all duration-150 hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)] focus-visible:ring-offset-1"
            >
                <span>Lihat semua tiket</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    class="h-3.5 w-3.5 transition-transform duration-150 group-hover:translate-x-0.5"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/[0.04]">
        @forelse ($newBugs as $bug)
            @if (!$loop->first)
                <div class="mx-5 border-t border-slate-200/70"></div>
            @endif

            <x-pm.bug-assignment-row :bug="$bug" :programmers="$programmers" />
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
                    Semua tiket sudah memiliki penanggung jawab
                </p>

                <p class="mt-1.5 max-w-xs text-sm leading-relaxed text-slate-500">
                    Tidak ada tiket yang menunggu penugasan saat ini.
                </p>

                <a
                    href="{{ route('pm.issues.index') }}"
                    class="mt-5 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition-all duration-150 hover:border-[rgba(138,11,78,0.18)] hover:text-[#8a0b4e] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgba(138,11,78,0.20)]"
                >
                    Lihat semua tiket
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2"
                        class="h-3.5 w-3.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        @endforelse
    </div>

</section>

@endsection