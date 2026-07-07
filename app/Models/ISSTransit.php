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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
