<?php

namespace App\Models;

use App\Libs\SunCalc;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Class ISSTransit
 *
 * Represents an International Space Station solar or lunar transit / conjunction event
 * calculated for a specific observation location.
 *
 * @property int $id
 * @property string $public_token Unique 16-character alphanumeric slug for public sharing
 * @property int $location_id Associated location ID
 * @property string $type Celestial body: 'sun' or 'moon'
 * @property Carbon $time Peak closest approach timestamp in UTC
 * @property float $separation_degrees Angular separation between ISS center and celestial center (degrees)
 * @property float $altitude_degrees Observer altitude angle above horizon (degrees)
 * @property float $azimuth_degrees Observer azimuth angle from true North (degrees)
 * @property bool $is_exact_transit True if ISS passes directly across the celestial disk
 * @property array<int, array{dx: float, dy: float}>|null $path_points Relative coordinate offsets for orbit diagram
 * @property Carbon|null $notified_at Timestamp when alert email was dispatched to user
 * @property int|null $cloud_cover_percent Forecasted percentage cloud cover at time of pass
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Location $location Associated location instance
 * @property-read string $event_title Human-readable pass headline
 * @property-read string $public_url Permalink URL to public shareable page
 * @property-read string $share_text Multi-line summary formatted for sharing
 * @property-read string $google_calendar_url One-click Google Calendar export link
 * @property-read string $whatsapp_share_url Direct WhatsApp message link
 * @property-read string $twitter_share_url Twitter/X intent sharing link
 *
 * @mixin Builder
 */
class ISSTransit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'iss_transits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'public_token',
        'location_id',
        'type',
        'time',
        'separation_degrees',
        'altitude_degrees',
        'azimuth_degrees',
        'is_exact_transit',
        'path_points',
        'notified_at',
        'cloud_cover_percent',
    ];

    /**
     * The "booted" method of the model.
     * Assigns a cryptographically secure random token for permalinks if empty.
     */
    protected static function booted(): void
    {
        static::creating(function (ISSTransit $transit) {
            if (empty($transit->public_token)) {
                $transit->public_token = Str::random(16);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'time' => 'datetime',
            'separation_degrees' => 'decimal:4',
            'altitude_degrees' => 'decimal:2',
            'azimuth_degrees' => 'decimal:2',
            'is_exact_transit' => 'boolean',
            'path_points' => 'array',
            'notified_at' => 'datetime',
            'cloud_cover_percent' => 'integer',
        ];
    }

    /**
     * Calculate Moon illumination and phase details at the time of this transit.
     *
     * @return array{name: string, emoji: string, illumination: int, phase: float}
     */
    public function getMoonPhase(): array
    {
        return SunCalc::getMoonPhase($this->time);
    }

    /**
     * Get the location for this transit.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the descriptive event title (e.g. "ISS Solar Transit — Lancaster").
     */
    public function getEventTitleAttribute(): string
    {
        $typeStr = $this->type === 'sun' ? 'Solar' : 'Lunar';
        $locName = $this->location ? $this->location->name : 'Observing Site';

        return "ISS {$typeStr} Transit — {$locName}";
    }

    /**
     * Get the public URL for sharing this transit.
     */
    public function getPublicUrlAttribute(): string
    {
        return $this->public_token
            ? route('transit.show', $this->public_token)
            : url('/');
    }

    /**
     * Generate structured share text for social media and messaging.
     */
    public function getShareTextAttribute(): string
    {
        $typeStr = $this->type === 'sun' ? 'Solar' : 'Lunar';
        $locName = $this->location ? $this->location->name : 'Observing Site';
        $formattedTime = Carbon::parse($this->time)->timezone(config('app.timezone', 'UTC'))->format('l, M jS \a\t H:i:s');
        $sepText = "Separation: {$this->separation_degrees}° (".($this->is_exact_transit ? 'True Transit' : 'Conjunction').')';
        $altText = "Alt: {$this->altitude_degrees}°, Az: {$this->azimuth_degrees}°";
        $cloudText = $this->cloud_cover_percent !== null ? "{$this->cloud_cover_percent}% cloud" : 'Cloud forecast: Pending';

        return "🛰️ ISS {$typeStr} Transit over {$locName}\n📅 {$formattedTime}\n🎯 {$sepText}\n🧭 {$altText}\n☁️ {$cloudText}\nModeled via Astronotify: {$this->public_url}";
    }

    /**
     * Start time formatted in ISO 8601 UTC for calendar ICS definitions.
     */
    public function getUtcIsoAttribute(): string
    {
        return Carbon::parse($this->time)->utc()->format('Ymd\THis\Z');
    }

    /**
     * End time (+15 mins) formatted in ISO 8601 UTC for calendar ICS definitions.
     */
    public function getUtcEndIsoAttribute(): string
    {
        return Carbon::parse($this->time)->addMinutes(15)->utc()->format('Ymd\THis\Z');
    }

    /**
     * Google Calendar 1-click event creation URL.
     */
    public function getGoogleCalendarUrlAttribute(): string
    {
        $startIso = $this->utc_iso;
        $endIso = $this->utc_end_iso;

        $locStr = $this->location
            ? "{$this->location->name} ({$this->location->latitude}, {$this->location->longitude})"
            : 'Observing Site';

        $gTitle = urlencode($this->event_title);
        $gDesc = urlencode($this->share_text);
        $gLoc = urlencode($locStr);

        return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gTitle}&dates={$startIso}/{$endIso}&details={$gDesc}&location={$gLoc}";
    }

    /**
     * WhatsApp direct sharing link.
     */
    public function getWhatsAppShareUrlAttribute(): string
    {
        return 'https://api.whatsapp.com/send?text='.urlencode($this->share_text);
    }

    /**
     * Twitter/X direct intent sharing link.
     */
    public function getTwitterShareUrlAttribute(): string
    {
        $typeStr = $this->type === 'sun' ? 'Solar' : 'Lunar';
        $locName = $this->location ? $this->location->name : 'Observing Site';
        $formattedTime = Carbon::parse($this->time)->format('l, M jS \a\t H:i:s');

        $text = "🛰️ Upcoming ISS {$typeStr} Transit over {$locName} on {$formattedTime}!\n\nModeled via @Astronotify:\n{$this->public_url}";

        return 'https://twitter.com/intent/tweet?text='.urlencode($text);
    }
}
