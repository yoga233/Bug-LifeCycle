{{--
    Project Manager Layout
    Modular: extends layouts.base + pakai partial topbar.
--}}
@extends('layouts.base')

@section('title', 'DevPanel Project Manager')

@section('body')
    <div class="min-h-screen bg-[#f8fafc]">
        @include('layouts.partials.topbar', [
            'homeRoute'             => 'pm.dashboard',
            'badge'                 => 'Project Manager',
            'brandLogo'             => asset('images/client/Pranala-logo-high.png'),
            'nav'                   => [
                ['label' => 'Dashboard',          'route' => 'pm.dashboard',    'active' => 'pm.dashboard', 'icon' => 'squares-2x2'],
                ['label' => 'Manajemen',          'route' => 'pm.management',   'active' => 'pm.management', 'icon' => 'folder'],
                ['label' => 'Kinerja Programmer', 'route' => 'pm.kinerja',      'active' => 'pm.kinerja', 'icon' => 'presentation-chart-line'],
            ],
            'notificationRoute'      => 'pm.notifications',
            'notificationReadRoute'  => 'pm.notifications.read',
            'notificationCount'      => $pmUnreadNotifications ?? 0,
            'notificationPreview'    => $pmNotificationPreview ?? [],
            'avatarRing'             => 'ring-[rgba(138,11,78,0.20)]',
            'avatarGradient'         => 'from-[#8a0b4e] to-[#b23a73]',
        ])

        {{-- Global toast feedback --}}
        <x-flash-toast />

        <main class="mx-auto max-w-6xl px-6 py-8">
            @yield('content')
        </main>
    </div>
@endsection