<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class WeatherCondition extends Model
{
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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
