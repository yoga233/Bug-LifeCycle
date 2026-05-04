@php
  $landingSectionBase = rtrim((string) ($landingSectionBase ?? ''), '#');
@endphp

<div class="mob" id="mob" role="dialog" aria-modal="true" aria-label="Navigation menu">
  <div class="mob-panel">
    <div class="mob-top">
      <a href="{{ route('client.landing') }}" class="mob-logo" aria-label="PRANALA BLMS — Bug Lifecycle Management System">
        <img src="{{ asset('images/client/Pranala-logo-high.png') }}" alt="PRANALA BLMS" class="mob-logo-image" />
        <span class="mob-brand-text">
          <span class="mob-wordmark">PRANALA <span class="mob-wordmark-accent">BLMS</span></span>
          <span class="mob-brand-tagline">Bug Lifecycle Management System</span>
        </span>
      </a>

      <button class="mob-x" id="mob-x" aria-label="Close menu" type="button">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <nav class="mob-nav" aria-label="Mobile navigation">
      <a href="{{ $landingSectionBase }}#hero" data-i18n="nav_home">Home</a>
      <a href="{{ $landingSectionBase }}#problems" data-i18n="nav_why">Why BLMS</a>
      <a href="{{ $landingSectionBase }}#hiw" data-i18n="nav_hiw">How It Works</a>
      <a href="{{ $landingSectionBase }}#features" data-i18n="nav_features">Features</a>
      <a href="{{ $landingSectionBase }}#faq" data-i18n="nav_faq">FAQ</a>
    </nav>

    <div class="mob-divider" aria-hidden="true"></div>

    <div class="mob-lang" role="group" aria-label="Select language">
      <span class="mob-lang-label">Language</span>
      <div class="mob-lang-btns">
        <button class="mob-lang-btn" id="mob-btn-en" onclick="setLang('en')" type="button">EN</button>
        <span class="mob-lang-sep" aria-hidden="true">/</span>
        <button class="mob-lang-btn" id="mob-btn-id" onclick="setLang('id')" type="button">ID</button>
      </div>
    </div>

    <div class="mob-footer">
      <a href="{{ route('client.tracking') }}" class="btn btn-ghost" data-i18n="nav_track">Track Ticket</a>
      <a href="{{ route('client.report') }}" class="btn btn-solid" data-i18n="nav_report">Report Bug</a>
    </div>
  </div>
</div>