<?php

namespace App\Models;

use App\Libs\SunCalc;
use App\Models\Traits\ClearsAdminDashboardCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WeatherCondition
 *
 * Stores nighttime meteorological forecast slices and evaluated stargazing suitability
 * for a specific user location and date.
 *
 * @property int $id
 * @property int $location_id Parent location ID
 * @property Carbon $date Target forecast night date
 * @property array<int, array{time: string, cloud: int, wind: float|int}>|null $forecast_data Nighttime hourly forecast points
 * @property array<string, int>|null $hourly_clouds Mapping of UTC and local hours to cloud percentage
 * @property bool $is_optimal Whether the night meets the location's criteria for stargazing
 * @property Carbon|null $notified_at Timestamp when alert email was dispatched
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Location $location Location instance
 *
 * @mixin Builder
 */
class WeatherCondition extends Model
{
    use ClearsAdminDashboardCache;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'date',
        'forecast_data',
        'hourly_clouds',
        'is_optimal',
        'notified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'forecast_data' => 'json',
            'hourly_clouds' => 'array',
            'is_optimal' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    /**
     * Calculate the Moon phase and illumination details for this forecast night.
     *
     * @return array{name: string, emoji: string, illumination: int, phase: float}
     */
    public function getMoonPhase(): array
    {
        return SunCalc::getMoonPhase($this->date);
    }

    /**
     * Get the parent location that this weather condition belongs to.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
