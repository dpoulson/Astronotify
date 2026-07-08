<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ISSTransit extends Model
{
    protected $table = 'iss_transits';

    protected $fillable = [
        'location_id',
        'type',
        'time',
        'separation_degrees',
        'altitude_degrees',
        'azimuth_degrees',
        'is_exact_transit',
        'path_points',
    ];

    protected function casts(): array
    {
        return [
            'time' => 'datetime',
            'separation_degrees' => 'decimal:4',
            'altitude_degrees' => 'decimal:2',
            'azimuth_degrees' => 'decimal:2',
            'is_exact_transit' => 'boolean',
            'path_points' => 'array',
        ];
    }

    public function getMoonPhase(): array
    {
        $date = $this->time;
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date)->toDateTime();
        } elseif ($date instanceof \Carbon\Carbon) {
            $date = $date->toDateTime();
        }
        
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
            'phase' => $phaseVal,
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
