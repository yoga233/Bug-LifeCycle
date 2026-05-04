<!DOCTYPE html>
<html lang="{{ $clientPortalLang ?? 'en' }}">
<head>
    @include('layouts.partials.portal-head', [
        'title'         => 'PRANALA BLMS — Ticket Tracking',
        'description'   => 'Check your bug report status and timeline. Enter your ticket ID to see the latest updates.',
        'includeStyles' => false,
    ])

    @vite([
        'resources/css/app.css',
        'resources/css/portal-landing.css',
        'resources/css/portal-tracking.css',
        'resources/js/app.js',
        'resources/js/portal-landing.js',
        'resources/js/portal-tracking-i18n.js',
    ])
</head>

<body class="tracking-page" data-page="client-tracking" data-i18n-ready-expected="2">

    @php $ticket = trim((string) $ticket); @endphp

    @include('portal.landing.partials.nav', [
        'landingSectionBase' => route('client.landing'),
    ])
    @include('portal.landing.partials.mobile-menu', [
        'landingSectionBase' => route('client.landing'),
    ])

    <main id="main" class="tracking-main">
        <section class="tracking-shell">
            <div class="wrap">
                <div class="tracking-flow">
                    <div class="tracking-stack">

                        @include('portal.tracking.partials.header')
                        @include('portal.tracking.partials.search-form')

                        <div class="tracking-result">
                            <div class="tracking-result-stack">

                                @if ($searched && $error)
                                    @include('portal.tracking.partials.error-alert')
                                @endif

                                @if ($bug)
                                    @include('portal.tracking.partials.bug-summary')
                                    @include('portal.tracking.partials.bug-timeline')
                                @elseif ($guestReport)
                                    @include('portal.tracking.partials.guest-summary')
                                @elseif (! $searched)
                                    @include('portal.tracking.partials.empty-state')
                                @endif

                            </div>
                        </div>

                        @include('portal.tracking.partials.actions')

                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('portal.report.partials.task-footer')

</body>
</html>