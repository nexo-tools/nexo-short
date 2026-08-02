<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the instance operator that a short link was reported (the backlog item
 * ADR-005 left open: "report → email").
 *
 * Until now a report was a row in a table nobody polled, which for an abuse
 * channel means the report may as well not exist: the whole point is that
 * somebody acts within hours.
 *
 * Operator-facing, so deliberately not translated — it goes to whoever runs the
 * instance, with the command they will want next.
 */
class LinkReported extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Report $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reported link: '.$this->report->slug);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.link-reported');
    }
}
