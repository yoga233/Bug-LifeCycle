<section id="hero" aria-labelledby="hero-heading">
  <div class="hero-deco-circle hero-deco-1" aria-hidden="true"></div>
  <div class="hero-deco-circle hero-deco-2" aria-hidden="true"></div>
  <div class="hero-grid" aria-hidden="true"></div>
  <div class="hero-line" aria-hidden="true"></div>

  <div class="hero-inner" id="main">
    <div class="hero-copy">
      <div class="hero-pill reveal">
        <span class="hero-pill-dot" aria-hidden="true"></span>
        <span data-i18n="hero_pill">Bug Lifecycle Management</span>
      </div>

      <h1 id="hero-heading" class="hero-h1 reveal d1">
        <span data-i18n="hero_h1a">Dari laporan bug</span><br/>
        <em data-i18n="hero_h1b">ke perbaikan terverifikasi</em><br/>
        <strong data-i18n="hero_h1c">transparan.</strong>
      </h1>

      <p class="hero-p reveal d2" data-i18n="hero_p">
        Laporkan ke tim engineering kami dan pantau setiap tahap secara real-time. Tidak ada lagi email yang tenggelam, tidak ada lagi ketidakjelasan status.
      </p>

      <div class="hero-btns reveal d3">
        <a href="{{ route('client.report') }}" class="btn btn-solid">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          <span data-i18n="cta_report">Laporkan Bug</span>
        </a>

        <a href="{{ route('client.tracking') }}" class="btn btn-ghost">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <span data-i18n="cta_track">Lacak Tiket</span>
        </a>
      </div>

      <div class="hero-feats reveal d4" aria-label="Fitur utama">
        <div class="hero-feat">
          <span class="hero-feat-dot" aria-hidden="true"></span>
          <span data-i18n="f_structured">Formulir Terstruktur</span>
        </div>

        <div class="hero-feat">
          <span class="hero-feat-dot" aria-hidden="true"></span>
          <span data-i18n="f_realtime">Pelacakan Real-time</span>
        </div>

        <div class="hero-feat">
          <span class="hero-feat-dot" aria-hidden="true"></span>
          <span data-i18n="f_audit">Jejak Audit Lengkap</span>
        </div>
      </div>
    </div>

    <div class="hero-vis" aria-hidden="true">
      <div class="hv-layout">
        <figure class="hv-item hv-item-top hv-enter" style="--hv-delay:0s;">
          <img
            src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=1600&q=85&auto=format&fit=crop"
            alt="Team discussing bug triage priorities"
            width="1600"
            height="900"
            fetchpriority="high"
            decoding="async"
            class="hv-img"
          />
        </figure>

        <figure class="hv-item hv-item-bottom-left hv-enter" style="--hv-delay:.15s;">
          <img
            src="https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=950&q=85&auto=format&fit=crop"
            alt="Developer focused while writing code"
            width="950"
            height="1180"
            loading="lazy"
            decoding="async"
            class="hv-img"
          />
        </figure>

        <figure class="hv-item hv-item-support hv-enter" style="--hv-delay:.30s;">
          <img
            src="https://images.unsplash.com/photo-1553877522-43269d4ea984?w=760&q=85&auto=format&fit=crop"
            alt="Team reviewing issue status board"
            width="760"
            height="950"
            loading="lazy"
            decoding="async"
            class="hv-img"
          />
        </figure>

        <figure class="hv-item hv-item-bottom-right hv-enter" style="--hv-delay:.45s;">
          <img
            src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=950&q=85&auto=format&fit=crop"
            alt="QA and engineering team confirming resolution"
            width="950"
            height="1180"
            loading="lazy"
            decoding="async"
            class="hv-img"
          />
        </figure>
      </div>
    </div>
  </div>

  <div class="scroll-ind" aria-hidden="true">
    <div class="scroll-ind-bar"></div>
    <div class="scroll-ind-txt">scroll</div>
  </div>
</section>