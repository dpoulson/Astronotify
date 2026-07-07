<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestConnectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sentAt;

    public function __construct($sentAt)
    {
        $this->sentAt = $sentAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Astronotify Mail Connection Test 🚀',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.test-connection',
        );
    }
}
