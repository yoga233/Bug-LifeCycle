{{--
    Client (Public) Layout
    Modular: extends layouts.base + pakai partial topbar.
--}}
@extends('layouts.base')

@section('title', 'PRANALA BLMS - Bug Portal')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        @include('layouts.partials.topbar', [
            'homeRoute' => 'client.landing',
            'badge' => 'Client',
            'brandLogo' => asset('images/client/Pranala-logo-high.png'),
            // Public landing does not need a dedicated nav menu. Keep it clean.
            'nav' => [],
            // Right-side CTA (PM-style): secondary (outline) + primary (solid)
            'secondaryCta' => ['label' => 'Track Ticket', 'route' => 'client.tracking', 'icon' => 'search'],
            'primaryCta' => ['label' => 'Report Bug', 'route' => 'client.report', 'icon' => 'plus'],
            'showInternalLoginLink' => false,
            // Public portal should not show internal auth menu (logout/avatar)
            'showAuthActions' => false,
        ])

        <main>
            @yield('content')
        </main>
    </div>
@endsection
