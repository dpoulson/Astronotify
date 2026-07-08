<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class WeatherCondition extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (WeatherCondition $condition) {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats_v2');
        });

        static::deleted(function (WeatherCondition $condition) {
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats_v2');
        });
    }

    protected $fillable = [
        'location_id',
        'date',
        'forecast_data',
        'is_optimal',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'forecast_data' => 'json',
            'is_optimal' => 'boolean',
        ];
    }

    public function getMoonPhase(): array
    {
        $date = \Carbon\Carbon::parse($this->date)->toDateTime();
        $illum = \App\Libs\SunCalc::getMoonIllumination($date);
        
        $phaseVal = $illum['phase'];
        $fraction = $illum['fraction'];

        if ($phaseVal < 0.03 || $phaseVal >= 0.97) {
            $name = 'New Moon';
            $emoji = '🌑';
        } elseif ($phaseVal < 0.22) {
            $name = 'Waxing Crescent';
            $emoji = '🌒';
        } elseif ($phaseVal < 0.28) {
            $name = 'First Quarter';
            $emoji = '🌓';
        } elseif ($phaseVal < 0.47) {
            $name = 'Waxing Gibbous';
            $emoji = '🌔';
        } elseif ($phaseVal < 0.53) {
            $name = 'Full Moon';
            $emoji = '🌕';
        } elseif ($phaseVal < 0.72) {
            $name = 'Waning Gibbous';
            $emoji = '🌖';
        } elseif ($phaseVal < 0.78) {
            $name = 'Last Quarter';
            $emoji = '🌗';
        } else {
            $name = 'Waning Crescent';
            $emoji = '🌘';
        }

        return [
            'name' => $name,
            'emoji' => $emoji,
            'illumination' => (int) round($fraction * 100),
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
