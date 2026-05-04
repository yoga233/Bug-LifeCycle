@extends('layouts.project-manager')

@section('title', 'Manajemen')

@section('content')
    <div>
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
            <span class="font-medium text-slate-600">Manajemen</span>
        </div>

        {{-- Page Title --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                    Manajemen
                </h1>
                <p class="mt-1.5 text-sm text-slate-500">
                    Satu halaman untuk mengelola Users, Projects, dan Master Data (Severities/Priorities).
                </p>
            </div>
        </div>

        {{-- Tabs + URL sync --}}
        <div
            x-data="{
                tab: (new URLSearchParams(window.location.search).get('tab') ?? 'users'),
                setTab(next) {
                    this.tab = next;
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', next);
                    history.pushState({ tab: next }, '', url);
                },
                init() {
                    const url = new URL(window.location.href);
                    if (!url.searchParams.get('tab')) {
                        url.searchParams.set('tab', this.tab);
                        history.replaceState({ tab: this.tab }, '', url);
                    }

                    window.addEventListener('popstate', (e) => {
                        const url2 = new URL(window.location.href);
                        this.tab = url2.searchParams.get('tab') ?? (e.state?.tab ?? 'users');
                    });
                }
            }"
            class="space-y-6"
        >
            @include('panel.project-manager.management.partials.tabs')
            @include('panel.project-manager.management.partials.team-section')
            @include('panel.project-manager.management.partials.masterdata-section')

            {{-- Keep modals available for both Users and Projects tabs --}}
            @include('panel.project-manager.team.partials.modals')
        </div>
    </div>
@endsection