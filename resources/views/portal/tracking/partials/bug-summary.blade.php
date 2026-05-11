<section class="tr-card" aria-labelledby="summary-heading">
    <div class="tr-card-body">

        <h2
            id="summary-heading"
            class="tr-section-title"
            data-tracking-i18n="tracking_summary_ticket_number"
        >
            Ticket ID
        </h2>

        <div class="tracking-summary-head">
            <div class="min-w-0">
                <p
                    class="tracking-ticket-label"
                    data-tracking-i18n="tracking_summary_ticket_number"
                >
                    Ticket ID
                </p>
                <p class="tracking-ticket-number">
                    {{ $ticket }}
                </p>
            </div>

            @php
                $displayStatus = $bug->status;
                // If internally 'In Progress' but previously reached 'Testing', show as 'Testing' to client
                if ($bug->status === 'In Progress') {
                    $hasReachedTesting = $bug->statusHistories->where('new_status', 'Testing')->isNotEmpty();
                    if ($hasReachedTesting) {
                        $displayStatus = 'Testing';
                    }
                }
            @endphp

            <x-status-badge
                :status="$displayStatus"
                class="self-start tracking-status-chip"
                data-tracking-status-value="{{ $displayStatus }}"
            >
                {{ $displayStatus }}
            </x-status-badge>
        </div>

        <h3 class="tracking-summary-title break-words">
            {{ $bug->title }}
        </h3>

        <p class="tracking-summary-subtitle">
            {{ $bug->project?->name ?? '-' }}
        </p>

        <div class="tracking-meta-grid">
            <p class="break-words">
                <span data-tracking-i18n="tracking_label_reporter">Submitted by:</span>
                {{ $bug->guest_name }}
            </p>
            <p>
                <span data-tracking-i18n="tracking_label_report_date">Submitted on:</span>
                {{ $bug->created_at?->format('d M Y, H:i') }}
            </p>
            <p>
                <span data-tracking-i18n="tracking_label_severity">Severity level:</span>
                {{ $bug->severity?->level ?? '-' }}
            </p>
            <p>
                <span data-tracking-i18n="tracking_label_priority">Priority:</span>
                @if ($bug->priority?->name)
                    {{ $bug->priority->name }}
                @else
                    <span data-tracking-i18n="tracking_priority_unset">
                        Being assigned by our team
                    </span>
                @endif
            </p>
        </div>

    </div>
</section>