<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StargazingSummary extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [30, 60, 120];
    
    public $alerts;
    public $userName;
    public $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($alerts, \App\Models\User $user)
    {
        $this->alerts = $alerts;
        $this->userName = $user->name;
        $this->unsubscribeUrl = \Illuminate\Support\Facades\URL::signedRoute('notifications.manage', ['user' => $user->id]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Upcoming Optimal Stargazing Nights 🌠',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.stargazing-summary',
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
