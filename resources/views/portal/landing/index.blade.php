<!DOCTYPE html>
<html lang="{{ $clientPortalLang ?? 'en' }}">
<head>
@include('layouts.partials.portal-head')
</head>
<body data-page="client-landing" data-i18n-ready-expected="1">

@include('portal.landing.partials.nav')
@include('portal.landing.partials.mobile-menu')
@include('portal.landing.sections.hero')
@include('portal.landing.sections.marquee')
@include('portal.landing.sections.problems')
@include('portal.landing.sections.how-it-works')
@include('portal.landing.sections.features')
@include('portal.landing.sections.faq')
@include('portal.landing.sections.footer')

@vite('resources/js/portal-landing.js')
</body>
</html>
