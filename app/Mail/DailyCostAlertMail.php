<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyCostAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Carbon     $date,
        public object     $summary,
        public float      $costPct,
        public float      $threshold,
        public Collection $items,
        public float      $itemThreshold,
        public object     $eod,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                '⚠️ Cost Alert %s — Cost %.1f%% (threshold %.0f%%)',
                $this->date->format('d M Y'),
                $this->costPct,
                $this->threshold
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-cost-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
