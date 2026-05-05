@extends('layouts.base')

@section('title', 'DevPanel QA')

@section('body')
    <div class="min-h-screen bg-[#f8fafc]">
        @include('layouts.partials.topbar', [
            'homeRoute'              => 'qa.testing-queue',
            'badge'                  => 'QA',
            'brandLogo'              => asset('images/client/Pranala-logo-high.png'),
            'nav'                    => [],
            'notificationRoute'      => 'qa.notifications',
            'notificationReadRoute'  => 'qa.notifications.read',
            'notificationCount'      => $qaUnreadNotifications ?? 0,
            'notificationPreview'    => $qaNotificationPreview ?? [],
            'avatarRing'             => 'ring-[rgba(138,11,78,0.20)]',
            'avatarGradient'         => 'from-[#8a0b4e] to-[#b23a73]',
        ])

        <x-flash-toast />

        <main class="mx-auto max-w-6xl px-6 py-8">
            @yield('content')
        </main>
    </div>
@endsection