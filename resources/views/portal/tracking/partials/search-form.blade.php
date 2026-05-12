@include('portal.tracking.partials.last-ticket-banner')

<section class="tr-card" aria-label="Ticket search">
    <div class="tr-card-body">

        <h2 class="tr-section-title" data-tracking-i18n="tracking_label_ticket_number">
            Enter your ticket ID
        </h2>

        <form
            method="GET"
            action="{{ route('client.tracking') }}"
            class="tracking-form"
            novalidate
        >
            <label
                for="ticket"
                class="sr-only"
                data-tracking-i18n="tracking_label_ticket_number"
            >
                Enter your ticket ID
            </label>

            <input
                id="ticket"
                name="ticket"
                value="{{ $ticket }}"
                autocomplete="off"
                spellcheck="false"
                class="tracking-input"
                placeholder="Example: BUG-8F3K2L"
                data-tracking-i18n-placeholder="tracking_placeholder_ticket"
            />

            <button type="submit" class="btn btn-solid tracking-btn-primary">
                <x-lucide name="search" class="h-4 w-4" />
                <span data-tracking-i18n="tracking_btn_track">Find Ticket</span>
            </button>
        </form>

        <p class="tracking-help" data-tracking-i18n="tracking_help">
            You received your ticket ID in the confirmation email after submitting
            your report. Check your spam folder if you cannot find it.
        </p>

    </div>
</section>

<script>
(function () {
    'use strict';

    var STORAGE_KEY = 'last_ticket';

    var banner   = document.getElementById('ltBanner');
    var display  = document.getElementById('ltBannerTicket');
    var copyBtn  = document.getElementById('ltCopyBtn');
    var copyLbl  = document.getElementById('ltCopyLabel');
    var trackBtn = document.getElementById('ltTrackBtn');
    var input    = document.getElementById('ticket');

    if (!banner || !display || !copyBtn || !trackBtn || !input) return;

    var ticket = '';

    try {
        ticket = (localStorage.getItem(STORAGE_KEY) || '').trim();
    } catch (e) {
        // localStorage blocked (private browsing / storage denied)
        return;
    }

    if (!ticket) return;

    // ── Show the banner ──────────────────────────────────────────
    display.textContent = ticket;
    banner.classList.add('lt-visible');
    banner.setAttribute('aria-hidden', 'false');

    // ── Copy button ──────────────────────────────────────────────
    copyBtn.addEventListener('click', function () {
        if (!navigator.clipboard) {
            // Fallback for older browsers
            try {
                var tmp = document.createElement('textarea');
                tmp.value = ticket;
                tmp.style.cssText = 'position:fixed;opacity:0';
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                document.body.removeChild(tmp);
            } catch (e) { return; }
            flashCopied();
            return;
        }
        navigator.clipboard.writeText(ticket).then(flashCopied).catch(function () {});
    });

    function flashCopied() {
        copyLbl.textContent = 'Tersalin!';
        copyBtn.disabled = true;
        setTimeout(function () {
            copyLbl.textContent = 'Salin Nomor';
            copyBtn.disabled = false;
        }, 1800);
    }

    // ── Track button ─────────────────────────────────────────────
    trackBtn.addEventListener('click', function () {
        input.value = ticket;
        input.focus();
        // Auto-submit the form so result loads immediately
        var form = input.closest('form');
        if (form) form.submit();
    });
}());
</script>