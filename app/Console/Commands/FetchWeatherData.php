<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Location;
use App\Models\WeatherCondition;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\StargazingAlert;
use Carbon\Carbon;

#[Signature('weather:fetch')]
#[Description('Fetches weather forecasts for registered stargazing locations.')]
class FetchWeatherData extends Command
{
    public function handle()
    {
        $locations = Location::query()->with('user')->where('is_active', true)->get();
        // Load dynamically from settings layout
        $grouping_decimals = (int) (\App\Models\Setting::where('key', 'grouping_decimal_places')->value('value') ?? 1);
        $forecast_days = (int) (\App\Models\Setting::where('key', 'forecast_days')->value('value') ?? 7);

        // Round to 1 decimal place (~11km accuracy) to aggressively batch close locations together into the same meteorological cell
        $groupedLocations = $locations->groupBy(fn(\App\Models\Location $l) => round($l->latitude, $grouping_decimals) . ',' . round($l->longitude, $grouping_decimals));

        $this->info("Fetching forecast for " . $groupedLocations->count() . " distinct coordinate zones...");

        foreach ($groupedLocations as $coords => $group) {
            $first = $group->first();

            // Track API Call (Grand Total)
            \Illuminate\Support\Facades\DB::table('system_metrics')->updateOrInsert(
                ['key' => 'weather_api_calls'],
                ['value' => \Illuminate\Support\Facades\DB::raw('value + 1'), 'updated_at' => now()]
            );

            // Track API Call (Daily)
            \Illuminate\Support\Facades\DB::table('daily_metrics')->updateOrInsert(
                ['key' => 'weather_api_calls', 'date' => now()->toDateString()],
                ['value' => \Illuminate\Support\Facades\DB::raw('value + 1'), 'updated_at' => now(), 'created_at' => now()]
            );

            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $first->latitude,
                'longitude' => $first->longitude,
                'hourly' => 'cloud_cover,wind_speed_10m',
                'daily' => 'sunrise,sunset',
                'timezone' => 'auto',
                'forecast_days' => $forecast_days + 1
            ]);

            if ($response->failed()) continue;

            $data = $response->json();
            
            // Loop through the configured nights dynamically
            for ($dayIndex = 0; $dayIndex < $forecast_days; $dayIndex++) {
                $sunsetStr = $data['daily']['sunset'][$dayIndex] ?? null;
                $sunriseStr = $data['daily']['sunrise'][$dayIndex + 1] ?? null;
                
                if (!$sunsetStr || !$sunriseStr) continue;

                $sunset = Carbon::parse($sunsetStr);
                $sunrise = Carbon::parse($sunriseStr);
                $dateStr = $sunset->toDateString();
                
                $nightLength = $sunset->diffInHours($sunrise);
                
                // Cache hourly arrays for this night slice to avoid redundant iteration per-user
                $nightHours = [];
                foreach ($data['hourly']['time'] as $index => $timeStr) {
                    $time = Carbon::parse($timeStr);
                    if ($time->between($sunset, $sunrise)) {
                        $nightHours[] = [
                            'cloud' => $data['hourly']['cloud_cover'][$index] ?? 100,
                            'wind' => $data['hourly']['wind_speed_10m'][$index] ?? 100,
                        ];
                    }
                }

                // Now evaluate this specific night against EVERY user strictly mapped to this coordinate bin
                foreach ($group as $location) {
                    $isOptimal = false;
                    $clearConsecutive = 0;
                    $maxClear = 0;

                    if ($nightLength >= $location->min_night_length_hours) {
                        foreach ($nightHours as $hourData) {
                            if ($hourData['cloud'] <= $location->max_cloud_cover && $hourData['wind'] <= $location->max_wind_speed) {
                                $clearConsecutive++;
                                if ($clearConsecutive > $maxClear) {
                                    $maxClear = $clearConsecutive;
                                }
                            } else {
                                $clearConsecutive = 0;
                            }
                        }
                        
                        if ($maxClear >= $location->min_clear_hours) {
                            $isOptimal = true;
                        }
                    }

                    $condition = WeatherCondition::where('location_id', $location->id)->whereDate('date', $dateStr)->first();
                    $alreadyWasOptimal = $condition ? $condition->is_optimal : false;

                    // Save to DB to cut down on API calls
                    if ($condition) {
                        $condition->update([
                            'forecast_data' => ['note' => 'data trimmed for DB performance'],
                            'is_optimal' => $isOptimal,
                        ]);
                    } else {
                        WeatherCondition::create([
                            'location_id' => $location->id,
                            'date' => Carbon::parse($dateStr),
                            'forecast_data' => ['note' => 'data trimmed for DB performance'],
                            'is_optimal' => $isOptimal,
                        ]);
                    }

                    // Notify if newly optimal
                    if ($isOptimal && !$alreadyWasOptimal) {
                        Mail::to($location->user->email)->send(new StargazingAlert($location, $nightLength, $maxClear, $dateStr));
                        $this->info("Alert queued for {$location->name} on {$dateStr}");
                    }
                }
            }
        }
        
        $this->info('Weather data fetch and batching complete.');
    }
}
