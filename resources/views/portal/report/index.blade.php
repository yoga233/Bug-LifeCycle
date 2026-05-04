<!DOCTYPE html>
<html lang="{{ $clientPortalLang ?? 'en' }}">
<head>
    @include('layouts.partials.portal-head', [
        'title' => 'PRANALA BLMS — Form Laporan Bug',
        'description' => 'Ajukan laporan bug terstruktur ke tim engineering PRANALA BLMS.',
        'includeStyles' => false,
    ])

    @vite([
        'resources/css/app.css',
        'resources/css/portal-landing.css',
        'resources/css/portal-report.css',
        'resources/js/app.js',
        'resources/js/portal-landing.js',
        'resources/js/portal-report-i18n.js',
        'resources/js/portal-report-annotator.js',
    ])
</head>
<body class="report-page" data-page="client-report" data-i18n-ready-expected="2">
    @include('portal.landing.partials.nav', [
        'landingSectionBase' => route('client.landing'),
    ])
    @include('portal.landing.partials.mobile-menu', ['landingSectionBase' => route('client.landing')])

    <main id="main" class="report-main">
        @include('portal.report.sections.form')
    </main>

    @include('portal.report.partials.task-footer')

</body>
</html>