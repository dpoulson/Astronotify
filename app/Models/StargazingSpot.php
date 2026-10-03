<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StargazingSpot extends Model
{
    use HasFactory;

    protected $table = 'stargazing_spots';

    protected $fillable = [
        'name',
        'slug',
        'country',
        'region',
        'latitude',
        'longitude',
        'elevation',
        'bortle_class',
        'dark_sky_status',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'elevation' => 'integer',
            'bortle_class' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StargazingSpot $spot) {
            if (empty($spot->slug)) {
                $base = Str::slug($spot->name);
                $slug = $base;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-" . (++$count);
                }
                $spot->slug = $slug;
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getBortleDescriptionAttribute(): string
    {
        return match ($this->bortle_class) {
            1 => 'Class 1: Excellent truly dark sky',
            2 => 'Class 2: Truly dark site with negligible glow',
            3 => 'Class 3: Rural sky with detailed Milky Way',
            4 => 'Class 4: Rural/suburban transition',
            5 => 'Class 5: Suburban sky with moderate light pollution',
            6 => 'Class 6: Bright suburban sky',
            7 => 'Class 7: Suburban/urban transition',
            8 => 'Class 8: City sky with faint stars only',
            9 => 'Class 9: Inner-city sky',
            default => "Class {$this->bortle_class}",
        };
    }

    public function getBortleColorAttribute(): string
    {
        return match ($this->bortle_class) {
            1 => 'text-emerald-400 bg-emerald-950/70 border-emerald-500/40',
            2 => 'text-teal-400 bg-teal-950/70 border-teal-500/40',
            3 => 'text-cyan-400 bg-cyan-950/70 border-cyan-500/40',
            4 => 'text-blue-400 bg-blue-950/70 border-blue-500/40',
            5 => 'text-yellow-400 bg-yellow-950/70 border-yellow-500/40',
            6 => 'text-amber-400 bg-amber-950/70 border-amber-500/40',
            default => 'text-orange-400 bg-orange-950/70 border-orange-500/40',
        };
    }
}
