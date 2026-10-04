<?php

namespace App\Livewire;

use App\Models\ISSTransit;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Class PublicTransitView
 *
 * Renders the public permalink pass prediction page with orbital chord diagram,
 * telemetry readout, calendar exports, and social share actions.
 */
class PublicTransitView extends Component
{
    /**
     * Transit public token identifier.
     */
    public string $token;

    /**
     * Resolved transit model.
     */
    public ?ISSTransit $transit = null;

    /**
     * Calculated Moon illumination and phase data.
     *
     * @var array{name: string, emoji: string, illumination: int, phase: float}
     */
    public array $moonPhase = [];

    public string $timeFormatted = '';

    public string $timeUtc = '';

    public string $timeIso = '';

    public string $endIso = '';

    public string $gCalUrl = '';

    public string $whatsAppUrl = '';

    public string $twitterUrl = '';

    public string $transitTitle = '';

    public string $transitDescription = '';

    public bool $isPast = false;

    /**
     * Mount and initialize the transit view by resolving public token or ID.
     *
     * @param  string  $token  Alphanumeric token or numeric transit ID.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->transit = ISSTransit::with('location')
            ->where('public_token', $token)
            ->first();

        // Fallback for numeric ID if legacy links are accessed
        if (! $this->transit && is_numeric($token)) {
            $this->transit = ISSTransit::with('location')->find((int) $token);
        }

        if (! $this->transit) {
            abort(404, 'Transit prediction not found or has expired.');
        }

        $this->moonPhase = $this->transit->getMoonPhase();

        $tTime = Carbon::parse($this->transit->time);
        $this->isPast = $tTime->isPast();
        $this->timeFormatted = $tTime->format('l, F jS, Y \a\t H:i:s T');
        $this->timeUtc = $tTime->utc()->format('Y-m-d H:i:s \U\T\C');
        $this->timeIso = $tTime->utc()->format('Ymd\THis\Z');
        $this->endIso = $tTime->copy()->addMinutes(15)->utc()->format('Ymd\THis\Z');

        $this->transitTitle = $this->transit->event_title;
        $this->transitDescription = $this->transit->share_text;
        $this->gCalUrl = $this->transit->google_calendar_url;
        $this->whatsAppUrl = $this->transit->whatsapp_share_url;
        $this->twitterUrl = $this->transit->twitter_share_url;
    }

    /**
     * Render the public transit view.
     */
    public function render(): View
    {
        return view('livewire.public-transit-view', [
            'transit' => $this->transit,
            'location' => $this->transit->location,
        ])->layout('layouts.guest');
    }
}
