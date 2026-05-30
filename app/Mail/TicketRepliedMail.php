<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRepliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket, public TicketReply $reply) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Reply on Your Ticket — ' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-replied',
            with: ['ticket' => $this->ticket, 'reply' => $this->reply],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
