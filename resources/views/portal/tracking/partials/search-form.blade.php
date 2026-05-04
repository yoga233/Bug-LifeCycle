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