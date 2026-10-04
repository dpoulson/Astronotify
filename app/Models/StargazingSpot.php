<?php

namespace App\Models;

use App\Libs\BortleScale;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class StargazingSpot
 *
 * Represents a public curated dark sky observation location.
 *
 * @property int $id
 * @property string $name Location name
 * @property string $slug Unique URL slug
 * @property string $country Country name
 * @property string|null $region State, province, or county
 * @property float $latitude Decimal latitude (-90 to +90)
 * @property float $longitude Decimal longitude (-180 to +180)
 * @property int|null $elevation Height above sea level in meters
 * @property int $bortle_class Bortle darkness scale rating (1-9)
 * @property string|null $dark_sky_status Formal IDA/DS designation (e.g. Dark Sky Park)
 * @property string|null $description Editorial summary and observing details
 * @property bool $is_active Visibility status in the public directory
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $bortle_description Human-readable Bortle class summary
 * @property-read string $bortle_color Tailwind CSS classes for darkness badge
 *
 * @method static Builder|StargazingSpot active() Scope query to active spots only
 *
 * @mixin Builder
 */
class StargazingSpot extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stargazing_spots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * The "booted" method of the model.
     * Generates a unique URL slug upon creation if not explicitly supplied.
     */
    protected static function booted(): void
    {
        static::creating(function (StargazingSpot $spot) {
            if (empty($spot->slug)) {
                $base = Str::slug($spot->name);
                $slug = $base;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-".(++$count);
                }
                $spot->slug = $slug;
            }
        });
    }

    /**
     * Scope a query to only include active public spots.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor for human-readable Bortle scale description.
     */
    public function getBortleDescriptionAttribute(): string
    {
        return BortleScale::description($this->bortle_class);
    }

    /**
     * Accessor for Bortle badge styling classes.
     */
    public function getBortleColorAttribute(): string
    {
        return BortleScale::badgeColor($this->bortle_class);
    }
}
