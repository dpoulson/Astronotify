<?php

namespace App\Providers;

use App\Models\CommandLog;
use App\Models\User;
use App\Services\DiscordWebhookService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Set up Predict include path and autoloader globally
        set_include_path(get_include_path() . PATH_SEPARATOR . base_path('app/Libs'));
        spl_autoload_register(function ($class) {
            if (strpos($class, 'Predict') === 0) {
                $file = base_path('app/Libs/' . str_replace('_', '/', $class) . '.php');
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MariaDB older indexing length limits on utf8mb4 configurations
        Schema::defaultStringLength(191);

        Gate::define('admin', function (User $user) {
            return $user->is_admin;
        });

        // Listen for new user registrations to broadcast Discord notification
        Event::listen(Registered::class, function (Registered $event) {
            try {
                if ($event->user instanceof User) {
                    app(DiscordWebhookService::class)->sendUserRegisteredNotification($event->user);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to dispatch Discord user registration webhook: ' . $e->getMessage());
            }
        });

        // Log Console Commands
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            try {
                $command = $event->command;
                
                // Exclude noise
                $exclude = ['serve', 'migrate', 'migrate:status', 'vendor:publish', 'package:discover', 'livewire:discover', 'queue:work', 'queue:listen'];
                if (in_array($command, $exclude) || is_null($command) || !Schema::hasTable('command_logs')) {
                    return;
                }

                CommandLog::create([
                    'command' => $command,
                    'status' => 'running',
                    'started_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Silently fail to not block the command
                Log::error('Command logging failed: ' . $e->getMessage());
            }
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            try {
                $command = $event->command;
                
                $exclude = ['serve', 'migrate', 'migrate:status', 'vendor:publish', 'package:discover', 'livewire:discover', 'queue:work', 'queue:listen'];
                if (in_array($command, $exclude) || is_null($command) || !Schema::hasTable('command_logs')) {
                    return;
                }

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
            } catch (\Exception $e) {
                // Silently fail
                Log::error('Command logging (finished) failed: ' . $e->getMessage());
            }
        });
    }
}
