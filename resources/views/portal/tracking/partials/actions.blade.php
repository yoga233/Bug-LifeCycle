<section class="tr-card" aria-label="Page actions">
    <div class="tr-card-body">
        <div class="tracking-actions">

            <a
                href="{{ route('client.landing') }}"
                class="btn btn-sm tracking-btn-secondary"
            >
                <x-lucide name="home" class="h-4 w-4" />
                <span data-tracking-i18n="tracking_btn_back">Back to Home</span>
            </a>

            <a
                href="{{ route('client.report') }}"
                class="btn btn-sm btn-solid tracking-btn-ghost"
            >
                <x-lucide name="plus" class="h-4 w-4" />
                <span data-tracking-i18n="tracking_btn_new_report">Submit Another Report</span>
            </a>

        </div>
    </div>
</section>