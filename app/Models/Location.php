<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Location extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Location $location) {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats_v2');
        });

        static::deleted(function (Location $location) {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats_v2');
        });
    }

    protected $fillable = [
        'user_id',
        'name',
        'latitude',
        'longitude',
        'elevation',
        'min_night_length_hours',
        'min_clear_hours',
        'max_wind_speed',
        'max_cloud_cover',
        'is_active',
        'notify_iss_sun_transit',
        'notify_iss_moon_transit',
        'notify_stargazing_alerts',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'elevation' => 'integer',
            'max_wind_speed' => 'decimal:2',
            'is_active' => 'boolean',
            'notify_iss_sun_transit' => 'boolean',
            'notify_iss_moon_transit' => 'boolean',
            'notify_stargazing_alerts' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conditions()
    {
        return $this->hasMany(WeatherCondition::class);
    }

    public function transits()
    {
        return $this->hasMany(ISSTransit::class);
    }

    public function reevaluateConditions(): void
    {
        foreach ($this->conditions()->get() as $condition) {
            $forecastHours = $condition->forecast_data;
            if (!is_array($forecastHours) || empty($forecastHours)) {
                continue;
            }

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

            $condition->update([
                'is_optimal' => $isOptimal,
            ]);
        }
    }
}
