@extends('layouts.project-manager')

@section('title', 'Master Data')

@section('content')
    <div class="space-y-6">

        {{-- Breadcrumb --}}
        <div class="mb-4 flex items-center gap-2 text-xs">
            <a href="{{ route('pm.dashboard') }}"
               class="text-slate-400 transition-colors hover:text-slate-700">
                Dashboard
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                 fill="currentColor" class="h-3 w-3 text-slate-300" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z"
                      clip-rule="evenodd" />
            </svg>
            <span class="font-medium text-slate-600">Master Data</span>
        </div>

        {{-- Page Title --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                    Master Data
                </p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                    Severities &amp; Priorities
                </h1>
                <p class="mt-1.5 text-sm text-slate-500">
                    Kelola daftar master yang dipakai di form bug report dan workflow internal.
                </p>
            </div>
        </div>

        @include('panel.project-manager.master-data.partials.content')

    </div>
@endsection