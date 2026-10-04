<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 *
 * Represents application-wide dynamic configuration key-value pairs stored in the database.
 *
 * @property int $id
 * @property string $key Configuration key name
 * @property string|null $value Configuration value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 */
class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * Retrieve a configuration setting by its key, or fallback to a default value.
     *
     * @param  string  $key  Setting key name.
     * @param  mixed  $default  Fallback value if setting key does not exist.
     * @return mixed Setting value or default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Store or update a configuration setting key and value.
     *
     * @param  string  $key  Setting key name.
     * @param  mixed  $value  Setting value to persist.
     * @return static Persisted setting model instance.
     */
    public static function set(string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if a configuration setting exists in the database.
     *
     * @param  string  $key  Setting key name.
     * @return bool True if key exists.
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }
}
