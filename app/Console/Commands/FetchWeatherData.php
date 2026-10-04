<?php

namespace App\Console\Commands;

use App\Mail\StargazingSummary;
use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\Setting;
use App\Models\WeatherCondition;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class FetchWeatherData
 *
 * Artisan command to poll Open-Meteo API forecasts, batch coordinate zones,
 * evaluate stargazing suitability, update transit cloud cover, and queue summaries.
 */
#[Signature('weather:fetch {--location= : Specific location ID to fetch for} {--no-alerts : Do not send alert emails}')]
#[Description('Fetches weather forecasts for registered stargazing locations.')]
class FetchWeatherData extends Command
{
    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success).
     */
    public function handle(): int
    {
        $locationId = $this->option('location');
        $query = Location::query()->with('user');

        if ($locationId) {
            $query->where('id', $locationId);
        } else {
            $query->where('is_active', true);
        }

        $locations = $query->get();
        if ($locations->isEmpty()) {
            $this->info('No locations found to fetch weather for.');

            return 0;
        }

        // Load configuration parameters
        $groupingDecimals = (int) Setting::get('grouping_decimal_places', 1);
        $forecastDays = (int) Setting::get('forecast_days', 7);

        $userAlerts = [];

        // Round coordinates to batch close locations into the same meteorological cell
        $groupedLocations = $locations->groupBy(
            fn (Location $l) => round($l->latitude, $groupingDecimals).','.round($l->longitude, $groupingDecimals)
        );

        $this->info("Fetching forecast for {$groupedLocations->count()} distinct coordinate zones...");

        foreach ($groupedLocations as $coords => $group) {
            $first = $group->first();

            // Track API metrics
            $this->recordApiCallMetric();

            // Open-Meteo free tier caps at 16 days. Buffer +2 for sunrise/sunset lookahead.
            $apiDays = min($forecastDays, 14) + 2;

            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $first->latitude,
                'longitude' => $first->longitude,
                'hourly' => 'cloud_cover,wind_speed_10m',
                'daily' => 'sunrise,sunset',
                'timezone' => 'auto',
                'forecast_days' => $apiDays,
            ]);

            if ($response->failed()) {
                $this->error("API failed for {$coords} (HTTP {$response->status()}) — skipping.");

                continue;
            }

            $data = $response->json();
            $tz = $data['timezone'] ?? 'UTC';
            $this->line("  {$coords} (tz={$tz}) — processing ".$group->pluck('name')->join(', ').'...');

            // Pre-process 24-hour hourly cloud cover for transits and conditions
            $hourlyCloudsByDate = [];
            if (isset($data['hourly']['time'], $data['hourly']['cloud_cover'])) {
                foreach ($data['hourly']['time'] as $idx => $timeStr) {
                    $localTime = Carbon::parse($timeStr, $tz);
                    $dateKey = $localTime->toDateString();
                    $cloud = (int) ($data['hourly']['cloud_cover'][$idx] ?? 100);

                    $localHour = $localTime->format('H:00');
                    $utcHour = $localTime->copy()->utc()->format('Y-m-d H:00');

                    $hourlyCloudsByDate[$dateKey][$localHour] = $cloud;
                    $hourlyCloudsByDate[$dateKey][$utcHour] = $cloud;
                    $hourlyCloudsByDate[$localTime->copy()->utc()->toDateString()][$utcHour] = $cloud;
                }
            }

            // Loop through configured nights (capped at 14 nights)
            $nightCount = min($forecastDays, 14);
            for ($dayIndex = 0; $dayIndex < $nightCount; $dayIndex++) {
                $sunsetStr = $data['daily']['sunset'][$dayIndex] ?? null;
                $sunriseStr = $data['daily']['sunrise'][$dayIndex + 1] ?? null;

                if (! $sunsetStr || ! $sunriseStr) {
                    continue;
                }

                $sunset = Carbon::parse($sunsetStr);
                $sunrise = Carbon::parse($sunriseStr);
                $dateStr = $sunset->toDateString();
                $nightLength = $sunset->diffInHours($sunrise);

                // Cache hourly arrays for this night slice
                $nightHours = [];
                foreach ($data['hourly']['time'] as $index => $timeStr) {
                    $time = Carbon::parse($timeStr);
                    if ($time->between($sunset, $sunrise)) {
                        $nightHours[] = [
                            'time' => $time->format('H:i'),
                            'cloud' => $data['hourly']['cloud_cover'][$index] ?? 100,
                            'wind' => $data['hourly']['wind_speed_10m'][$index] ?? 100,
                        ];
                    }
                }

                // Evaluate night against every location in this coordinate bin
                foreach ($group as $location) {
                    $evaluation = $location->evaluateForecastHours($nightHours);
                    $isOptimal = $evaluation['is_optimal'];
                    $maxClear = $evaluation['max_clear'];

                    $condition = WeatherCondition::where('location_id', $location->id)
                        ->whereDate('date', $dateStr)
                        ->first();
                    $alreadyNotified = $condition && ! is_null($condition->notified_at);

                    try {
                        if ($condition) {
                            $condition->update([
                                'forecast_data' => $nightHours,
                                'hourly_clouds' => $hourlyCloudsByDate[$dateStr] ?? null,
                                'is_optimal' => $isOptimal,
                            ]);
                        } else {
                            $condition = WeatherCondition::create([
                                'location_id' => $location->id,
                                'date' => Carbon::parse($dateStr),
                                'forecast_data' => $nightHours,
                                'hourly_clouds' => $hourlyCloudsByDate[$dateStr] ?? null,
                                'is_optimal' => $isOptimal,
                            ]);
                        }
                    } catch (Exception $e) {
                        $this->error("  Failed to save condition for {$location->name} on {$dateStr}: ".$e->getMessage());
                        Log::error("weather:fetch DB error for location {$location->id} on {$dateStr}: ".$e->getMessage());
                    }

                    // Collect alert data if optimal, not yet notified, and alerts enabled
                    if ($isOptimal && ! $alreadyNotified && $location->notify_stargazing_alerts) {
                        $userAlerts[$location->user_id]['user'] = $location->user;
                        $userAlerts[$location->user_id]['condition_ids'][] = $condition->id;
                        $userAlerts[$location->user_id]['alerts'][] = [
                            'location_name' => $location->name,
                            'date' => $dateStr,
                            'night_length' => $nightLength,
                            'max_clear' => $maxClear,
                        ];
                    }
                }
            }

            // Refresh cloud cover predictions on upcoming ISS transits for this location
            foreach ($group as $location) {
                $upcomingTransits = ISSTransit::where('location_id', $location->id)
                    ->where('time', '>=', now())
                    ->get();

                foreach ($upcomingTransits as $transit) {
                    $cloud = $location->getCloudCoverAt($transit->time);
                    if ($cloud !== null && $transit->cloud_cover_percent !== $cloud) {
                        $transit->update(['cloud_cover_percent' => $cloud]);
                    }
                }
            }
        }

        // Send alert emails
        $noAlerts = (bool) $this->option('no-alerts');
        if (! $noAlerts && count($userAlerts) > 0) {
            $this->info('Queuing aggregated summary emails for '.count($userAlerts).' users...');
            foreach ($userAlerts as $userId => $data) {
                $user = $data['user'];
                $alerts = $data['alerts'];
                $conditionIds = $data['condition_ids'] ?? [];

                try {
                    Mail::to($user->email)->queue(new StargazingSummary($alerts, $user));
                    WeatherCondition::whereIn('id', $conditionIds)->update(['notified_at' => now()]);
                    $this->info("Summary alert queued for {$user->name} (".count($alerts).' nights)');
                } catch (Exception $e) {
                    Log::error("Failed to queue weather summary email for User ID {$userId}: ".$e->getMessage());
                    $this->error("Failed to queue email for {$user->name}: ".$e->getMessage());
                }
            }
        }

        // Prune historical weather records older than today
        if (! $locationId) {
            WeatherCondition::where('date', '<', today()->toDateString())->delete();
        }

        $this->info('Weather data fetch and batching complete.');

        return 0;
    }

    /**
     * Atomically record weather API call metrics in system and daily tracking tables.
     */
    private function recordApiCallMetric(): void
    {
        DB::table('system_metrics')->updateOrInsert(
            ['key' => 'weather_api_calls'],
            ['updated_at' => now()]
        );
        DB::table('system_metrics')->where('key', 'weather_api_calls')->increment('value');

        DB::table('daily_metrics')->updateOrInsert(
            ['key' => 'weather_api_calls', 'date' => now()->toDateString()],
            ['updated_at' => now()]
        );
        DB::table('daily_metrics')->where('key', 'weather_api_calls')->where('date', now()->toDateString())->increment('value');
    }
}
