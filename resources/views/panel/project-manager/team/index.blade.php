@extends('layouts.project-manager')

@section('title', 'Team & Projects')

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
            <span class="font-medium text-slate-600">Team &amp; Projects</span>
        </div>

        {{-- Page Title --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                    Manajemen User &amp; Project
                </h1>
                <p class="mt-1.5 text-sm text-slate-500">
                    Kelola anggota tim, peran, dan penugasan project di satu tempat.
                </p>
            </div>
        </div>

        @php
            $roleBadge = fn (string $role) => match($role) {
                'Programmer'      => 'border border-[#8a0b4e]/20 bg-[#f5e8ef] text-[#8a0b4e]',
                'Project Manager' => 'border border-[#8a0b4e]/20 bg-[#f5e8ef] text-[#8a0b4e]',
                'QA'              => 'border border-emerald-100 bg-emerald-50 text-emerald-700',
                default           => 'border border-slate-200 bg-slate-50 text-slate-600',
            };

            $projectBadge = fn () => 'border border-emerald-100 bg-emerald-50 text-emerald-700';
        @endphp

        @include('panel.project-manager.team.partials.toggle')
        @include('panel.project-manager.team.partials.users-section')
        @include('panel.project-manager.team.partials.projects-section')
        @include('panel.project-manager.team.partials.modals')
    </div>
@endsection