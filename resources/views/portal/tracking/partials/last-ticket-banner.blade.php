{{--
    Last Ticket Banner
    – Hidden by default via CSS (display:none on .lt-banner)
    – JS reads localStorage key "last_ticket" and adds class .lt-visible
    – Placed above the search form so it's the first thing user sees
--}}
<div
    id="ltBanner"
    class="lt-banner"
    role="region"
    aria-label="Your last reported ticket"
    aria-hidden="true"
>
    {{-- Icon --}}
    <span class="lt-banner-icon" aria-hidden="true">
        {{-- bookmark / ticket icon via inline SVG for zero dependency --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
            <path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>
        </svg>
    </span>

    {{-- Label + ticket number (populated by JS) --}}
    <div class="lt-banner-body">
        <p class="lt-banner-label">Last Reported Ticket</p>
        <p class="lt-banner-ticket" id="ltBannerTicket">—</p>
    </div>

    {{-- Action buttons --}}
    <div class="lt-banner-actions">
        <button
            type="button"
            id="ltCopyBtn"
            class="lt-btn lt-btn-copy"
            aria-label="Copy ticket number"
        >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
            </svg>
            <span id="ltCopyLabel">Salin Nomor</span>
        </button>

        <button
            type="button"
            id="ltTrackBtn"
            class="lt-btn lt-btn-track"
            aria-label="Track this ticket now"
        >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            Lacak Sekarang
        </button>
    </div>
</div>
