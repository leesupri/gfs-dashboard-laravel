<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket Status Updated — ' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-status-changed',
            with: [
                'ticket'    => $this->ticket,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
