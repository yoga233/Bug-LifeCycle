<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BugReportedTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $ticket,
        public readonly string $guestName,
        public readonly string $title,
        public readonly string $trackingUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tiket Bug Report Anda: '.$this->ticket,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.bug-reported-ticket',
            with: [
                'ticket' => $this->ticket,
                'guestName' => $this->guestName,
                'title' => $this->title,
                'trackingUrl' => $this->trackingUrl,
            ],
        );
    }
}
