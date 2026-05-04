{{--
    Programmer Layout
    Modular: extends layouts.base + pakai partial topbar.
--}}
@extends('layouts.base')

@section('title', 'DevPanel Programmer')

@section('body')
    <div class="min-h-screen bg-[#f8fafc]">
        @include('layouts.partials.topbar', [
            'homeRoute'            => 'programmer.dashboard',
            'badge'                => 'Programmer',
            'brandLogo'            => asset('images/client/Pranala-logo-high.png'),
            'nav'                  => [],
            'notificationRoute'    => 'programmer.notifications',
            'notificationReadRoute'=> 'programmer.notifications.read',
            'notificationCount'    => $programmerUnreadNotifications ?? 0,
            'notificationPreview'  => $programmerNotificationPreview ?? [],
            'avatarRing'           => 'ring-[rgba(138,11,78,0.20)]',
            'avatarGradient'       => 'from-[#8a0b4e] to-[#b23a73]',
        ])

        <x-flash-toast />

        <main class="mx-auto max-w-6xl px-6 py-8">
            @yield('content')
        </main>
    </div>
@endsection