<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class BugReportedTicketMail extends Mailable implements ShouldQueue
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

    public function __construct(
        public readonly string $ticket,
        public readonly string $guestName,
        public readonly string $title,
        public readonly string $trackingUrl,
    ) {}

    public function headers(): Headers
    {
        return new Headers(
            text: [
                // RFC 2369 — required by Gmail/Yahoo bulk sender policy (2024+)
                'List-Unsubscribe' => '<mailto:' . config('mail.from.address') . '?subject=unsubscribe>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                // Precedence: bulk signals to spam filters this is automated (not forged personal mail)
                'Precedence' => 'bulk',
                // Organization header for identity
                'X-Mailer' => 'Bug-LifeCycle/1.0',
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->ticket . '] Your Bug Report Has Been Received',
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
            view: 'mail.bug-reported-ticket',
            text: 'mail.bug-reported-ticket-text',
            with: [
                'ticket'      => $this->ticket,
                'guestName'   => $this->guestName,
                'title'       => $this->title,
                'trackingUrl' => $this->trackingUrl,
            ],
        );
    }
}
