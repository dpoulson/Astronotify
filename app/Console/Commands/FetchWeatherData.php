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

        foreach ($locations as $location) {
            $metric = \Illuminate\Support\Facades\DB::table('system_metrics')->where('key', 'weather_api_calls')->first();
            if ($metric) {
                \Illuminate\Support\Facades\DB::table('system_metrics')->where('key', 'weather_api_calls')->increment('value');
            } else {
                \Illuminate\Support\Facades\DB::table('system_metrics')->insert([
                    'key' => 'weather_api_calls',
                    'value' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'hourly' => 'cloud_cover,wind_speed_10m',
                'daily' => 'sunrise,sunset',
                'timezone' => 'auto',
                'forecast_days' => 8
            ]);

            if ($response->failed()) continue;

            $data = $response->json();
            
            // Loop through the next 7 nights
            for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
                $sunsetStr = $data['daily']['sunset'][$dayIndex] ?? null;
                $sunriseStr = $data['daily']['sunrise'][$dayIndex + 1] ?? null;
                
                if (!$sunsetStr || !$sunriseStr) continue;

                $sunset = Carbon::parse($sunsetStr);
                $sunrise = Carbon::parse($sunriseStr);
                $dateStr = $sunset->toDateString(); // Date the night begins
                
                $nightLength = $sunset->diffInHours($sunrise);
                
                $isOptimal = false;
                $clearConsecutive = 0;
                $maxClear = 0;

                if ($nightLength >= $location->min_night_length_hours) {
                    // Check hourly forecast between sunset and sunrise
                    foreach ($data['hourly']['time'] as $index => $timeStr) {
                        $time = Carbon::parse($timeStr);
                        if ($time->between($sunset, $sunrise)) {
                            $cloud = $data['hourly']['cloud_cover'][$index] ?? 100;
                            $wind = $data['hourly']['wind_speed_10m'][$index] ?? 100;

                            if ($cloud <= $location->max_cloud_cover && $wind <= $location->max_wind_speed) {
                                $clearConsecutive++;
                                if ($clearConsecutive > $maxClear) {
                                    $maxClear = $clearConsecutive;
                                }
                            } else {
                                $clearConsecutive = 0;
                            }
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
                    $condition = WeatherCondition::create([
                        'location_id' => $location->id,
                        'date' => Carbon::parse($dateStr),
                        'forecast_data' => ['note' => 'data trimmed for DB performance'],
                        'is_optimal' => $isOptimal,
                    ]);
                }

                // Notify if newly optimal
                if ($isOptimal && !$alreadyWasOptimal) {
                    Mail::to($location->user->email)->send(new StargazingAlert($location, $nightLength, $maxClear, $dateStr));
                    $this->info("Alert sent for {$location->name} on {$dateStr}");
                }
            }
        }
    }
}
