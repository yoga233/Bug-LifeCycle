@extends('layouts.qa')

@section('title', 'Profil')

@section('content')
    {{-- Minimalist Header --}}
    <div class="max-w-2xl mb-12 text-left">
        <nav class="mb-6">
            <a href="{{ route('qa.testing-queue') }}" class="group inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-slate-400 transition-colors hover:text-[#8a0b4e]">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:-translate-x-1"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </nav>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Pengaturan Akun</h1>
        <p class="mt-2 text-sm text-slate-500">Kelola identitas dan keamanan personal Anda sebagai QA.</p>
    </div>

    <div class="max-w-2xl space-y-12 pb-32">
        {{-- Section: Profile --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Informasi Profil</h2>
                <p class="mt-1 text-sm text-slate-500">Perbarui nama publik dan alamat email akun Anda.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-left">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        {{-- Section: Password --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Kata Sandi</h2>
                <p class="mt-1 text-sm text-slate-500">Pastikan akun Anda tetap aman dengan password yang kuat.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-left">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        {{-- Section: Danger Zone --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-bold text-rose-600">Hapus Akun</h2>
                <p class="mt-1 text-sm text-slate-500">Tindakan permanen untuk menghapus seluruh data Anda.</p>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-rose-50/30 p-6 shadow-sm text-left">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
@endsection
