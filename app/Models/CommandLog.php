<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommandLog
 *
 * Records execution telemetry, exit status, and exception traces for scheduled
 * artisan commands and background crons.
 *
 * @property int $id
 * @property string $command Command signature (e.g. weather:fetch)
 * @property string $status Status descriptor ('running', 'success', 'failed')
 * @property int|null $exit_code Exit code returned by the command
 * @property int|null $duration_ms Total execution duration in milliseconds
 * @property string|null $exception Serialized exception message or stack trace
 * @property Carbon|null $started_at Timestamp when command started
 * @property Carbon|null $finished_at Timestamp when command concluded
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 */
class CommandLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'command',
        'status',
        'exit_code',
        'duration_ms',
        'exception',
        'started_at',
        'finished_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exit_code' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
