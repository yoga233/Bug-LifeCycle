<section class="tr-card" aria-labelledby="timeline-heading">
    <div class="tr-card-body">

        <h2
            id="timeline-heading"
            class="tr-section-title"
            data-tracking-i18n="tracking_timeline_title"
        >
            What has happened so far
        </h2>

        <div class="tracking-timeline-list" role="list">
            @forelse ($bug->statusHistories as $history)
                @php
                    $oldStatus = (string) $history->old_status;
                    $newStatus = (string) $history->new_status;

                    $statusRank = [
                        'Reported'    => 1,
                        'Assigned'    => 2,
                        'In Progress' => 3,
                        'Testing'     => 4,
                        'Resolved'    => 5,
                        'Closed'      => 6,
                    ];

                    $isRollback = isset($statusRank[$oldStatus], $statusRank[$newStatus])
                        && $statusRank[$newStatus] < $statusRank[$oldStatus];

                    $historyKind = match (true) {
                        $oldStatus === $newStatus                                 => 'created',
                        $oldStatus === 'Assigned' && $newStatus === 'Reported'   => 'assignment_canceled',
                        $oldStatus === 'Testing'  && $newStatus === 'In Progress' => 'testing_revision',
                        $isRollback                                               => 'rollback',
                        default                                                   => 'updated',
                    };
                @endphp

                <div
                    class="tracking-timeline-item"
                    role="listitem"
                >
                    <p
                        class="tracking-timeline-text break-words"
                        data-tracking-history-message
                        data-history-kind="{{ $historyKind }}"
                        data-old-status="{{ $oldStatus }}"
                        data-new-status="{{ $newStatus }}"
                    >
                        @if ($oldStatus === $newStatus)
                            Your report was received and is now in the queue.
                        @elseif ($oldStatus === 'Assigned' && $newStatus === 'Reported')
                            Your report is back in the review queue.
                        @elseif ($oldStatus === 'Testing' && $newStatus === 'In Progress')
                            The fix is being revised before final verification.
                        @else
                            Status was updated to
                            <span class="font-semibold text-[var(--p)]">
                                {{ $newStatus }}
                            </span>.
                        @endif
                    </p>

                    <p class="tracking-timeline-time">
                        {{ $history->changed_at?->format('d M Y, H:i') }}
                    </p>
                </div>

            @empty
                <p
                    class="tracking-empty-note"
                    data-tracking-i18n="tracking_timeline_empty"
                >
                    No updates yet. Your report has been received and is waiting to be reviewed.
                </p>
            @endforelse
        </div>

    </div>
</section>