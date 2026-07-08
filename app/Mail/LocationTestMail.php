<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Location;

class LocationTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $location;
    public $userName;

    public function __construct(Location $location, $userName)
    {
        $this->location = $location;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Astronotify Test Alert for {$this->location->name} 📡",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.location-test',
        );
    }
}
