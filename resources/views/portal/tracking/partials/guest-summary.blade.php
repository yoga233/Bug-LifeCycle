@php
    $queueStatus = strtolower((string) $guestReport->queue_status);

    $queueBadgeClass = match ($queueStatus) {
        'approved' => 'tracking-queue-badge--approved',
        'rejected' => 'tracking-queue-badge--rejected',
        'expired'  => 'tracking-queue-badge--expired',
        default    => 'tracking-queue-badge--pending',
    };

    $queueLabelFallback = match ($queueStatus) {
        'approved' => 'Accepted',
        'rejected' => 'Needs More Detail',
        'expired'  => 'No Longer Active',
        default    => 'Pending Review',
    };

    $queueNoteFallback = match ($queueStatus) {
        'approved' => 'Your report has been reviewed and accepted. It is now being processed by our engineering team.',
        'rejected' => 'Our team reviewed your report and needs more information to continue. Please submit a new report with additional details.',
        'expired'  => 'Your report is no longer active because it was not processed within the review window. Please submit a new report if the issue still occurs.',
        default    => 'Your report has been received and is waiting to be reviewed. We will notify you by email once it has been reviewed.',
    };

    $guestReportedAt = $guestReport->reported_at ?? $guestReport->created_at;
@endphp

<section class="tr-card" aria-labelledby="guest-summary-heading">
    <div class="tr-card-body">

        <h2
            id="guest-summary-heading"
            class="tr-section-title"
            data-tracking-i18n="tracking_summary_public_ticket"
        >
            Public Report Ticket
        </h2>

        <div class="tracking-summary-head">
            <div class="min-w-0">
                <p
                    class="tracking-ticket-label"
                    data-tracking-i18n="tracking_summary_public_ticket"
                >
                    Public Report Ticket
                </p>
                <p class="tracking-ticket-number">
                    {{ $ticket }}
                </p>
            </div>

            <span
                class="tracking-queue-badge {{ $queueBadgeClass }}"
                data-tracking-queue-label
                data-queue-status="{{ $queueStatus }}"
            >
                {{ $queueLabelFallback }}
            </span>
        </div>

        <h3 class="tracking-summary-title break-words">
            {{ $guestReport->title }}
        </h3>

        <p class="tracking-summary-subtitle">
            {{ $guestReport->project?->name ?? '-' }}
        </p>

        <div class="tracking-meta-grid">
            <p class="break-words">
                <span data-tracking-i18n="tracking_label_reporter">Submitted by:</span>
                {{ $guestReport->guest_name }}
            </p>
            <p class="break-words">
                <span data-tracking-i18n="tracking_label_email">Email:</span>
                {{ $guestReport->guest_email }}
            </p>
            <p>
                <span data-tracking-i18n="tracking_label_report_date">Submitted on:</span>
                {{ $guestReportedAt?->format('d M Y, H:i') }}
            </p>
            <p>
                <span data-tracking-i18n="tracking_label_severity">Severity level:</span>
                {{ $guestReport->severity?->level ?? '-' }}
            </p>
        </div>

        <div class="tracking-queue-note">
            <p
                data-tracking-queue-note
                data-queue-status="{{ $queueStatus }}"
            >
                {{ $queueNoteFallback }}
            </p>

            @if ($guestReport->processed_at)
                <div
                    class="tracking-meta-muted"
                    data-tracking-processed-at
                    data-value="{{ $guestReport->processed_at->format('d M Y, H:i') }}"
                >
                    Reviewed on {{ $guestReport->processed_at->format('d M Y, H:i') }}.
                </div>
            @endif

            @if ($guestReport->pm_notes)
                <div class="tracking-meta-muted">
                    <span
                        class="tracking-note-label"
                        data-tracking-i18n="tracking_pm_notes_label"
                    >
                        Note from our team:
                    </span>
                    {{ $guestReport->pm_notes }}
                </div>
            @endif
        </div>

    </div>
</section>