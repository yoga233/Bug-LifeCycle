@php
  $landingSectionBase = rtrim((string) ($landingSectionBase ?? ''), '#');
  $defaultLang = $clientPortalLang ?? 'en';
@endphp

<nav id="nav" role="navigation" aria-label="Main navigation">
  <a href="{{ route('client.landing') }}" class="nav-logo" aria-label="PRANALA BLMS — Bug Lifecycle Management System">
    <img src="{{ asset('images/client/Pranala-logo-high.png') }}" alt="PRANALA BLMS" class="nav-logo-image" />
    <span class="nav-brand-text">
      <span class="nav-wordmark">PRANALA <span class="nav-wordmark-accent">BLMS</span></span>
      <span class="nav-brand-tagline">Bug Lifecycle Management System</span>
    </span>
  </a>

  <div class="nav-links">
    <a href="{{ $landingSectionBase }}#hero" data-i18n="nav_home">Home</a>
    <a href="{{ $landingSectionBase }}#problems" data-i18n="nav_why">Why BLMS</a>
    <a href="{{ $landingSectionBase }}#hiw" data-i18n="nav_hiw">How It Works</a>
    <a href="{{ $landingSectionBase }}#features" data-i18n="nav_features">Features</a>
    <a href="{{ $landingSectionBase }}#faq" data-i18n="nav_faq">FAQ</a>
  </div>

  <div class="nav-right">
    <div class="lang-switch" role="group" aria-label="Select language">
      <button
        class="lang-link {{ $defaultLang === 'en' ? 'active' : '' }}"
        id="btn-en"
        aria-pressed="{{ $defaultLang === 'en' ? 'true' : 'false' }}"
        onclick="setLang('en')"
        type="button"
      >EN</button>

      <span class="lang-sep" aria-hidden="true">/</span>

      <button
        class="lang-link {{ $defaultLang === 'id' ? 'active' : '' }}"
        id="btn-id"
        aria-pressed="{{ $defaultLang === 'id' ? 'true' : 'false' }}"
        onclick="setLang('id')"
        type="button"
      >ID</button>
    </div>

    <div class="nav-cta">
      <a href="{{ route('client.tracking') }}" class="btn btn-ghost" data-i18n="nav_track">Track Ticket</a>
      <a href="{{ route('client.report') }}" class="btn btn-solid" data-i18n="nav_report">Report Bug</a>
    </div>
  </div>

  <button class="nav-ham" id="ham" aria-label="Open menu" aria-expanded="false" aria-controls="mob" type="button">
    <span></span><span></span><span></span>
  </button>
</nav>