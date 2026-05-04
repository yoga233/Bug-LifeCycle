<section class="tr-card" aria-live="polite">
    <div class="tr-card-body">

        <div class="tracking-alert" role="alert">
            <div class="flex items-start gap-3">

                <span class="tracking-alert-icon" aria-hidden="true">
                    <x-lucide name="alert-circle" class="h-4 w-4" />
                </span>

                <div class="min-w-0">
                    <p class="tracking-alert-title" data-tracking-i18n="tracking_error_title">
                        We could not find that ticket
                    </p>

                    @php
                        $errorTranslationKey = match ($error) {
                            'Ticket not found.'                                    => 'tracking_error_not_found',
                            'Invalid ticket format. Example: BUG-8F3K2L'          => 'tracking_error_invalid_format',
                            default                                                => '',
                        };
                    @endphp

                    <p
                        class="tracking-alert-text break-words"
                        data-tracking-error-code="{{ $errorTranslationKey }}"
                    >
                        {{ $error }}
                    </p>
                </div>

            </div>
        </div>

    </div>
</section>