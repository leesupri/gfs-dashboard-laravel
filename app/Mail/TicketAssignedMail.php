<?php

namespace App\Mail;

use App\Models\StaffUser;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket    $ticket,
        public StaffUser $assignee,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Action Required] Ticket ' . $this->ticket->ticket_number . ' assigned to you',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-assigned',
            with: [
                'ticket'   => $this->ticket,
                'assignee' => $this->assignee,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
