<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\CommandFinished;
use App\Models\CommandLog;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MariaDB older indexing length limits on utf8mb4 configurations
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        \Illuminate\Support\Facades\Gate::define('admin', function (\App\Models\User $user) {
            return $user->is_admin;
        });

        // Log Console Commands
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $command = $event->command;
            
            // Exclude noise
            $exclude = ['serve', 'migrate:status', 'vendor:publish', 'package:discover', 'livewire:discover', 'queue:work', 'queue:listen'];
            if (in_array($command, $exclude) || is_null($command)) {
                return;
            }

            CommandLog::create([
                'command' => $command,
                'status' => 'running',
                'started_at' => now(),
            ]);
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            $command = $event->command;
            
            // Should match the starting log
            $log = CommandLog::where('command', $command)
                ->where('status', 'running')
                ->orderBy('id', 'desc')
                ->first();

            if ($log) {
                $finishedAt = now();
                $duration = $log->started_at->diffInMilliseconds($finishedAt);
                
                $log->update([
                    'status' => $event->exitCode === 0 ? 'success' : 'failed',
                    'exit_code' => $event->exitCode,
                    'finished_at' => $finishedAt,
                    'duration_ms' => $duration,
                ]);
            }
        });
    }
}
