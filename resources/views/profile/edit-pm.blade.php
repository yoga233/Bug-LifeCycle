@extends('layouts.project-manager')

@section('title', 'Profil')

@section('content')
    {{-- Page Header (selaras dengan PM > Management) --}}
    <div class="mb-8">
        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Project Manager</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Profil</h1>
        <p class="mt-2 text-sm text-slate-500">Kelola informasi akun, ubah password, dan pengaturan keamanan.</p>
    </div>

    <div class="space-y-6">
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl">
            <div class="p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 shadow-sm rounded-xl">
            <div class="p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 shadow-sm rounded-xl">
            <div class="p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
