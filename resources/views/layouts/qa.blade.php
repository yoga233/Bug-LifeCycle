{{--
    QA Layout
    Modular: extends layouts.base + pakai partial topbar.
--}}
@extends('layouts.base')

@section('title', 'DevPanel QA')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        @include('layouts.partials.topbar', [
            'homeRoute' => 'qa.testing-queue',
            'badge' => 'QA',
            'brandLogo' => asset('images/client/Pranala-logo-high.png'),
            'nav' => [],
            'notificationRoute' => 'qa.notifications',
            'notificationReadRoute' => 'qa.notifications.read',
            'notificationCount' => $qaUnreadNotifications ?? 0,
            'notificationPreview' => $qaNotificationPreview ?? [],
            'avatarRing' => 'ring-[rgba(138,11,78,0.20)]',
            'avatarGradient' => 'from-[#8a0b4e] to-[#b23a73]',
        ])

        {{-- Global toast feedback for QA pages --}}
        <x-flash-toast />

        <main class="max-w-6xl mx-auto px-6 py-8">
            @yield('content')
        </main>
    </div>
@endsection
