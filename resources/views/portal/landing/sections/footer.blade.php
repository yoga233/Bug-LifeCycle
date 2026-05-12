@php
  $landingSectionBase = rtrim((string) ($landingSectionBase ?? ''), '#');
@endphp

<footer role="contentinfo">
  <div class="foot-top">
    <div class="foot-inner">
      <!-- Brand -->
      <div class="foot-brand">
        <a href="{{ route('client.landing') }}" class="foot-logo" aria-label="PRANALA BLMS — Bug Lifecycle Management System">
          <img src="{{ asset('images/client/Pranala-logo-high.png') }}" alt="PRANALA BLMS" class="foot-logo-image" />
          <span class="foot-brand-text">
            <span class="foot-wm">PRANALA <span class="foot-wm-accent">BLMS</span></span>
            <span class="foot-brand-tagline">Bug Lifecycle Management System</span>
          </span>
        </a>

        <p class="foot-tagline" data-i18n="foot_tagline">
          Portal pelaporan bug dan pelacakan tiket untuk klien PRANALA BLMS.
        </p>

        <div class="foot-contact">
          <a href="mailto:support@pranala.id">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"/>
            </svg>
            support@pranala.id
          </a>
        </div>
      </div>

      <!-- Portal Links -->
      <div>
        <div class="foot-col-h" data-i18n="foot_nav_h">Portal</div>
        <nav class="foot-links" aria-label="Portal navigation">
          <a href="{{ $landingSectionBase }}#hero" data-i18n="nav_home">Home</a>
          <a href="{{ $landingSectionBase }}#problems" data-i18n="nav_why">Kenapa BLMS</a>
          <a href="{{ $landingSectionBase }}#hiw" data-i18n="nav_hiw">Cara Kerja</a>
          <a href="{{ $landingSectionBase }}#features" data-i18n="nav_features">Fitur</a>
          <a href="{{ $landingSectionBase }}#faq" data-i18n="nav_faq">FAQ</a>
        </nav>
      </div>

      <!-- Actions -->
      <div>
        <div class="foot-col-h" data-i18n="foot_act_h">Aksi</div>
        <nav class="foot-links" aria-label="Portal actions">
          <a href="{{ route('client.report') }}" data-i18n="foot_report">Laporkan Bug</a>
          <a href="{{ route('client.tracking') }}" data-i18n="foot_track">Lacak Tiket</a>
          <a href="{{ route('login') }}" data-i18n="foot_signin">Login Internal</a>
        </nav>
      </div>

      <!-- Support -->
      <div>
        <div class="foot-col-h" data-i18n="foot_sup_h">Dukungan</div>
        <div class="foot-response">
          <div class="foot-resp-indicator">
            <div class="foot-resp-dot" aria-hidden="true"></div>
            <span class="foot-resp-text" data-i18n="foot_resp">Tim aktif selama jam kerja</span>
          </div>
          <p class="foot-resp-sub" data-i18n="foot_resp_sub">
            Setiap laporan masuk ke antrean penanganan dan diproses sesuai prioritas.
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="foot-bottom">
    <div class="foot-bottom-inner">
      <div class="foot-copy">
        © 2026 PRANALA BLMS. <span data-i18n="foot_rights">Semua hak dilindungi.</span>
      </div>

      <nav class="foot-legal" aria-label="Legal">
        <a href="#" data-i18n="foot_privacy">Kebijakan Privasi</a>
        <a href="#" data-i18n="foot_terms">Ketentuan Penggunaan</a>
      </nav>
    </div>
  </div>
</footer>