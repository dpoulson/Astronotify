<?php

namespace App\Models;

use App\Libs\BortleScale;
use App\Models\Traits\ClearsAdminDashboardCache;
use App\Services\DiscordWebhookService;
use App\Services\ISSTransitCalculator;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class Location
 *
 * Represents an observation location configured by a user with custom
 * weather thresholds and transit alert preferences.
 *
 * @property int $id
 * @property int $user_id Owner user ID
 * @property string $name Friendly location name
 * @property float $latitude Latitude in decimal degrees
 * @property float $longitude Longitude in decimal degrees
 * @property int|null $elevation Elevation in meters above sea level
 * @property int|null $bortle Bortle scale rating (1-9)
 * @property int $min_night_length_hours Minimum required night duration in hours
 * @property int $min_clear_hours Minimum consecutive clear hours needed for optimal status
 * @property float $max_wind_speed Maximum acceptable wind speed (km/h)
 * @property int $max_cloud_cover Maximum acceptable cloud cover percentage (0-100)
 * @property bool $is_active Whether automatic notifications/updates are active
 * @property bool $notify_iss_sun_transit Alert preference for ISS solar transits
 * @property bool $notify_iss_moon_transit Alert preference for ISS lunar transits
 * @property bool $notify_stargazing_alerts Alert preference for optimal stargazing night forecasts
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user Owner user instance
 * @property-read Collection<int, WeatherCondition> $conditions Weather condition records
 * @property-read Collection<int, WeatherCondition> $weatherConditions Alias for conditions
 * @property-read Collection<int, ISSTransit> $transits Modeled ISS transit events
 * @property-read Collection<int, ISSTransit> $issTransits Alias for transits
 * @property-read string $bortle_description Human-readable Bortle description
 * @property-read string $bortle_color Tailwind CSS classes for Bortle badge
 *
 * @mixin Builder
 */
class Location extends Model
{
    use ClearsAdminDashboardCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'latitude',
        'longitude',
        'elevation',
        'bortle',
        'min_night_length_hours',
        'min_clear_hours',
        'max_wind_speed',
        'max_cloud_cover',
        'is_active',
        'notify_iss_sun_transit',
        'notify_iss_moon_transit',
        'notify_stargazing_alerts',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'elevation' => 'integer',
            'bortle' => 'integer',
            'max_wind_speed' => 'decimal:2',
            'is_active' => 'boolean',
            'notify_iss_sun_transit' => 'boolean',
            'notify_iss_moon_transit' => 'boolean',
            'notify_stargazing_alerts' => 'boolean',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Location $location) {
            try {
                app(DiscordWebhookService::class)->sendLocationCreatedNotification($location);
            } catch (Throwable $e) {
                Log::warning('Failed to dispatch Discord location webhook: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get the user that owns the location.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the weather condition forecasts for this location.
     *
     * @return HasMany<WeatherCondition, $this>
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(WeatherCondition::class);
    }

    /**
     * Alias for conditions relation.
     *
     * @return HasMany<WeatherCondition, $this>
     */
    public function weatherConditions(): HasMany
    {
        return $this->conditions();
    }

    /**
     * Get the modeled ISS transits for this location.
     *
     * @return HasMany<ISSTransit, $this>
     */
    public function transits(): HasMany
    {
        return $this->hasMany(ISSTransit::class);
    }

    /**
     * Alias for transits relation.
     *
     * @return HasMany<ISSTransit, $this>
     */
    public function issTransits(): HasMany
    {
        return $this->transits();
    }

    /**
     * Accessor for human-readable Bortle scale description.
     */
    public function getBortleDescriptionAttribute(): string
    {
        return BortleScale::description($this->bortle);
    }

    /**
     * Accessor for Bortle badge styling classes.
     */
    public function getBortleColorAttribute(): string
    {
        return BortleScale::badgeColor($this->bortle);
    }

    /**
     * Evaluates a sequence of night forecast hours against this location's criteria.
     *
     * @param  array<int, array{time: string, cloud: int, wind: float|int}>  $forecastHours
     * @return array{is_optimal: bool, max_clear: int, clear_consecutive: int}
     */
    public function evaluateForecastHours(array $forecastHours): array
    {
        $isOptimal = false;
        $clearConsecutive = 0;
        $maxClear = 0;
        $nightLength = count($forecastHours);

        if ($nightLength >= $this->min_night_length_hours) {
            foreach ($forecastHours as $hourData) {
                $cloud = $hourData['cloud'] ?? 100;
                $wind = $hourData['wind'] ?? 100;

                if ($cloud <= $this->max_cloud_cover && $wind <= $this->max_wind_speed) {
                    $clearConsecutive++;
                    if ($clearConsecutive > $maxClear) {
                        $maxClear = $clearConsecutive;
                    }
                } else {
                    $clearConsecutive = 0;
                }
            }

            if ($maxClear >= $this->min_clear_hours) {
                $isOptimal = true;
            }
        }

        return [
            'is_optimal' => $isOptimal,
            'max_clear' => $maxClear,
            'clear_consecutive' => $clearConsecutive,
        ];
    }

    /**
     * Re-evaluates all saved weather conditions for this location using current thresholds.
     */
    public function reevaluateConditions(): void
    {
        foreach ($this->conditions()->get() as $condition) {
            $forecastHours = $condition->forecast_data;
            if (! is_array($forecastHours) || empty($forecastHours)) {
                continue;
            }

            $evaluation = $this->evaluateForecastHours($forecastHours);

            $condition->update([
                'is_optimal' => $evaluation['is_optimal'],
            ]);
        }
    }

    /**
     * Synchronize ISS transits and weather forecast data immediately for this location.
     *
     * @param  bool  $suppressAlerts  Whether to suppress email alerts during sync.
     */
    public function syncWeatherAndTransits(bool $suppressAlerts = true): void
    {
        try {
            $calculator = new ISSTransitCalculator;
            $calculator->calculateForLocation($this);
        } catch (Throwable $e) {
            Log::warning("Failed to calculate ISS transits for location {$this->id}: {$e->getMessage()}");
        }

        if (! app()->runningUnitTests()) {
            try {
                Artisan::call('weather:fetch', [
                    '--location' => $this->id,
                    '--no-alerts' => $suppressAlerts,
                ]);
            } catch (Throwable $e) {
                Log::warning("Failed to fetch weather for location {$this->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Resolve the forecasted cloud cover percentage at a specific date and time.
     *
     * @param  DateTimeInterface  $time  Target timestamp to lookup.
     * @return int|null Cloud cover percentage (0-100) or null if unforecasted.
     */
    public function getCloudCoverAt(DateTimeInterface $time): ?int
    {
        $carbon = Carbon::instance($time);
        $dateStr = $carbon->toDateString();

        $condition = $this->conditions()->whereDate('date', $dateStr)->first();
        if (! $condition) {
            $condition = $this->conditions()
                ->whereBetween('date', [$carbon->copy()->subDay()->toDateString(), $carbon->copy()->addDay()->toDateString()])
                ->first();
        }

        if (! $condition || empty($condition->hourly_clouds)) {
            return null;
        }

        $clouds = $condition->hourly_clouds;
        if (! is_array($clouds)) {
            return null;
        }

        // Try exact UTC hour key "YYYY-MM-DD HH:00"
        $utcKey = $carbon->copy()->utc()->format('Y-m-d H:00');
        if (isset($clouds[$utcKey])) {
            return (int) $clouds[$utcKey];
        }

        // Try rounded UTC hour key
        $roundedUtcKey = $carbon->copy()->utc()->roundHour()->format('Y-m-d H:00');
        if (isset($clouds[$roundedUtcKey])) {
            return (int) $clouds[$roundedUtcKey];
        }

        // Try local hour format "HH:00"
        $hourKey = $carbon->format('H:00');
        if (isset($clouds[$hourKey])) {
            return (int) $clouds[$hourKey];
        }

        return null;
    }
}
