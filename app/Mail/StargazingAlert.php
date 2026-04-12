<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StargazingAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $location;
    public $nightLength;
    public $maxClear;
    public $optimalDate;

    /**
     * Create a new message instance.
     */
    public function __construct($location, $nightLength, $maxClear, $optimalDate)
    {
        $this->location = $location;
        $this->nightLength = $nightLength;
        $this->maxClear = $maxClear;
        $this->optimalDate = $optimalDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Stargazing Alert',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.stargazing-alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
