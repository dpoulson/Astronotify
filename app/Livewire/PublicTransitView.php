<?php

namespace App\Livewire;

use App\Models\ISSTransit;
use Carbon\Carbon;
use Livewire\Component;

class PublicTransitView extends Component
{
    public string $token;
    public ?ISSTransit $transit = null;
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

    public function mount(string $token)
    {
        $this->token = $token;

        $this->transit = ISSTransit::with('location')
            ->where('public_token', $token)
            ->first();

        // Fallback for numeric ID if someone accessed via ID
        if (!$this->transit && is_numeric($token)) {
            $this->transit = ISSTransit::with('location')->find((int) $token);
        }

        if (!$this->transit) {
            abort(404, 'Transit prediction not found or has expired.');
        }

        $this->moonPhase = $this->transit->getMoonPhase();

        $tTime = Carbon::parse($this->transit->time);
        $this->isPast = $tTime->isPast();
        $this->timeFormatted = $tTime->format('l, F jS, Y \a\t H:i:s T');
        $this->timeUtc = $tTime->utc()->format('Y-m-d H:i:s \U\T\C');
        $this->timeIso = $tTime->utc()->format('Ymd\THis\Z');
        $this->endIso = $tTime->copy()->addMinutes(15)->utc()->format('Ymd\THis\Z');

        $typeStr = $this->transit->type === 'sun' ? 'Solar' : 'Lunar';
        $locName = $this->transit->location ? $this->transit->location->name : 'Observing Site';

        $this->transitTitle = "ISS {$typeStr} Transit over {$locName}";
        
        $sepText = "Separation: {$this->transit->separation_degrees}° (" . ($this->transit->is_exact_transit ? "True Transit" : "Conjunction") . ")";
        $altText = "Alt: {$this->transit->altitude_degrees}°, Az: {$this->transit->azimuth_degrees}°";
        $cloudText = $this->transit->cloud_cover_percent !== null ? "Cloud Cover: {$this->transit->cloud_cover_percent}%" : "Cloud forecast: Pending";

        $this->transitDescription = "🛰️ {$this->transitTitle}\n📅 {$this->timeFormatted}\n🎯 {$sepText}\n🧭 {$altText}\n☁️ {$cloudText}\nModeled via Astronotify: " . url('/transit/' . $this->transit->public_token);

        // Google Calendar URL
        $gTitle = urlencode($this->transitTitle);
        $gDesc = urlencode($this->transitDescription);
        $gLoc = urlencode($locName);
        $this->gCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gTitle}&dates={$this->timeIso}/{$this->endIso}&details={$gDesc}&location={$gLoc}";

        // Social Share URLs
        $this->whatsAppUrl = "https://api.whatsapp.com/send?text=" . urlencode($this->transitDescription);
        $this->twitterUrl = "https://twitter.com/intent/tweet?text=" . urlencode("🛰️ Upcoming ISS {$typeStr} Transit over {$locName} on {$this->timeFormatted}!\n\nModeled via @Astronotify:\n" . url('/transit/' . $this->transit->public_token));
    }

    public function render()
    {
        return view('livewire.public-transit-view', [
            'transit' => $this->transit,
            'location' => $this->transit->location,
        ])->layout('layouts.guest');
    }
}
