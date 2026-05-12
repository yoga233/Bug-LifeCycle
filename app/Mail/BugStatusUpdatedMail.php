<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class BugStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 60;

    /**
     * Client-facing status labels (English).
     * Only 5 statuses are shown to clients.
     */
    private const STATUS_LABELS = [
        'Reported'    => 'Reported',
        'Assigned'    => 'Assigned',
        'In Progress' => 'In Progress',
        'Testing'     => 'Under QA Review',
        'Resolved'    => 'Resolved',
    ];

    /**
     * Subject line messages per new status (English).
     */
    private const STATUS_SUBJECTS = [
        'Assigned'    => 'Your bug has been assigned to a programmer',
        'In Progress' => 'A programmer is working on your bug',
        'Testing'     => 'Your bug is being tested by our QA team',
        'Resolved'    => 'Your bug has been resolved',
    ];

    public function __construct(
        public readonly string $ticket,
        public readonly string $guestName,
        public readonly string $bugTitle,
        public readonly string $newStatus,
        public readonly string $trackingUrl,
    ) {}

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe'      => '<mailto:' . config('mail.from.address') . '?subject=unsubscribe>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'Precedence'            => 'bulk',
                'X-Mailer'              => 'Bug-LifeCycle/1.0',
            ],
        );
    }

    public function envelope(): Envelope
    {
        $subject = self::STATUS_SUBJECTS[$this->newStatus]
            ?? 'Your bug report status has been updated';

        return new Envelope(
            subject: '[' . $this->ticket . '] ' . $subject,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address(
                    config('mail.from.address'),
                    config('mail.from.name'),
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.bug-status-updated',
            text: 'mail.bug-status-updated-text',
            with: [
                'ticket'      => $this->ticket,
                'guestName'   => $this->guestName,
                'bugTitle'    => $this->bugTitle,
                'newStatus'   => $this->newStatus,
                'statusLabel' => self::STATUS_LABELS[$this->newStatus] ?? $this->newStatus,
                'headline'    => self::STATUS_SUBJECTS[$this->newStatus] ?? 'Your bug report status has been updated',
                'trackingUrl' => $this->trackingUrl,
                'progress'    => $this->resolveProgress($this->newStatus),
            ],
        );
    }

    /**
     * Map status → progress percentage for the visual progress bar.
     */
    private function resolveProgress(string $status): int
    {
        return match ($status) {
            'Reported'    => 10,
            'Assigned'    => 30,
            'In Progress' => 55,
            'Testing'     => 80,
            'Resolved'    => 100,
            default       => 0,
        };
    }

    /**
     * Get the client-facing status label.
     */
    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }
}
