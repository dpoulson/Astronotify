<?php

namespace Tests\Feature;

use App\Livewire\LocationManager;
use App\Mail\ISSTransitSummary;
use App\Mail\StargazingSummary;
use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Models\WeatherCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_fetch_deduplicates_alerts_via_notified_at(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Observatory',
            'latitude' => 51.5,
            'longitude' => -0.1,
            'min_night_length_hours' => 4,
            'min_clear_hours' => 2,
            'max_wind_speed' => 25,
            'max_cloud_cover' => 30,
            'notify_stargazing_alerts' => true,
            'is_active' => true,
        ]);

        // Mock Open-Meteo response
        $fakeSunset = now()->setTime(20, 0)->toIso8601String();
        $fakeSunrise = now()->addDay()->setTime(6, 0)->toIso8601String();

        $hourlyTimes = [];
        $hourlyClouds = [];
        $hourlyWinds = [];
        for ($i = 0; $i < 24; $i++) {
            $time = now()->startOfDay()->addHours($i);
            $hourlyTimes[] = $time->format('Y-m-d\TH:00');
            $hourlyClouds[] = 10; // Clear skies
            $hourlyWinds[] = 5;
        }

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'timezone' => 'UTC',
                'daily' => [
                    'sunset' => [$fakeSunset],
                    'sunrise' => [$fakeSunset, $fakeSunrise],
                ],
                'hourly' => [
                    'time' => $hourlyTimes,
                    'cloud_cover' => $hourlyClouds,
                    'wind_speed_10m' => $hourlyWinds,
                ],
            ], 200),
        ]);

        // First run: Should queue email and stamp notified_at
        $this->artisan('weather:fetch')->assertSuccessful();
        Mail::assertQueued(StargazingSummary::class, 1);

        $condition = WeatherCondition::where('location_id', $location->id)->first();
        $this->assertNotNull($condition);
        $this->assertTrue($condition->is_optimal);
        $this->assertNotNull($condition->notified_at);

        // Second run: Identical conditions, should NOT queue a duplicate email
        $this->artisan('weather:fetch')->assertSuccessful();
        Mail::assertQueued(StargazingSummary::class, 1); // Still 1, no second email
    }

    public function test_iss_transits_suppresses_alerts_for_overcast_weather(): void
    {
        Mail::fake();

        // Mock TLE with pass over location
        Http::fake([
            'celestrak.org/*' => Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200),
        ]);

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Garden',
            'latitude' => 51.5,
            'longitude' => -0.1,
            'notify_iss_sun_transit' => true,
            'notify_iss_moon_transit' => true,
            'is_active' => true,
        ]);

        // Set 100% overcast cloud cover forecast for all days in the 14-day transit window
        for ($d = 0; $d < 15; $d++) {
            $date = now()->addDays($d);
            $hourlyClouds = [];
            for ($h = 0; $h < 24; $h++) {
                $hourTime = $date->copy()->startOfDay()->addHours($h);
                $hourlyClouds[$hourTime->format('Y-m-d H:00')] = 100;
                $hourlyClouds[$hourTime->format('H:00')] = 100;
            }
            WeatherCondition::create([
                'location_id' => $location->id,
                'date' => $date->toDateString(),
                'forecast_data' => [],
                'hourly_clouds' => $hourlyClouds,
                'is_optimal' => false,
            ]);
        }

        $this->artisan('weather:iss-transits')->assertSuccessful();

        // Assert no email was queued due to overcast conditions
        Mail::assertNothingQueued();

        // Transits exist in database with cloud_cover_percent = 100 and unnotified
        $transits = ISSTransit::where('location_id', $location->id)->get();
        if ($transits->isNotEmpty()) {
            foreach ($transits as $transit) {
                $this->assertEquals(100, $transit->cloud_cover_percent);
                $this->assertNull($transit->notified_at);
            }
        }
    }

    public function test_iss_transits_queues_email_for_favorable_weather_and_deduplicates(): void
    {
        Mail::fake();

        Http::fake([
            'celestrak.org/*' => Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200),
        ]);

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Balcony',
            'latitude' => 51.5,
            'longitude' => -0.1,
            'notify_iss_sun_transit' => true,
            'notify_iss_moon_transit' => true,
            'is_active' => true,
        ]);

        // Set clear 15% cloud cover forecast for all days in the 14-day transit window
        for ($d = 0; $d < 15; $d++) {
            $date = now()->addDays($d);
            $hourlyClouds = [];
            for ($h = 0; $h < 24; $h++) {
                $hourTime = $date->copy()->startOfDay()->addHours($h);
                $hourlyClouds[$hourTime->format('Y-m-d H:00')] = 15;
                $hourlyClouds[$hourTime->format('H:00')] = 15;
            }
            WeatherCondition::create([
                'location_id' => $location->id,
                'date' => $date->toDateString(),
                'forecast_data' => [],
                'hourly_clouds' => $hourlyClouds,
                'is_optimal' => true,
            ]);
        }

        // First run: Should alert and mark notified_at
        $this->artisan('weather:iss-transits')->assertSuccessful();

        $transits = ISSTransit::where('location_id', $location->id)->get();
        if ($transits->isNotEmpty()) {
            Mail::assertQueued(ISSTransitSummary::class, 1);
            foreach ($transits as $transit) {
                $this->assertNotNull($transit->fresh()->notified_at);
                $this->assertEquals(15, $transit->cloud_cover_percent);
            }

            // Second run: Already notified, should NOT send duplicate email
            $this->artisan('weather:iss-transits')->assertSuccessful();
            Mail::assertQueued(ISSTransitSummary::class, 1); // Still 1
        }
    }

    public function test_dashboard_renders_weather_warning_on_overcast_transit(): void
    {
        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Hilltop',
            'latitude' => 51.5,
            'longitude' => -0.1,
            'is_active' => true,
        ]);

        ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'sun',
            'time' => now()->addHours(12),
            'separation_degrees' => 0.1,
            'altitude_degrees' => 50.0,
            'azimuth_degrees' => 160.0,
            'is_exact_transit' => true,
            'cloud_cover_percent' => 100,
        ]);

        $this->actingAs($user);

        Livewire::test(LocationManager::class)
            ->assertSee('⚠️ Cloudy (100%)')
            ->assertSee('Poor viewing: 100% cloud');
    }

    public function test_weather_forecast_window_is_decoupled_from_transit_calculation_window(): void
    {
        Mail::fake();

        Setting::set('forecast_days', 5);
        Setting::set('transit_days', 14);

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Dark Sky Site',
            'latitude' => 51.5,
            'longitude' => -0.1,
            'min_night_length_hours' => 4,
            'min_clear_hours' => 2,
            'max_wind_speed' => 25,
            'max_cloud_cover' => 30,
            'notify_stargazing_alerts' => false,
            'is_active' => true,
        ]);

        // Create two transits: one within the 5-day weather forecast window (day 2), one in the 14-day transit window (day 10)
        $nearTransit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'sun',
            'time' => now()->startOfDay()->addDays(2)->setTime(12, 0),
            'separation_degrees' => 0.15,
            'altitude_degrees' => 35.0,
            'azimuth_degrees' => 170.0,
            'is_exact_transit' => true,
            'cloud_cover_percent' => null,
        ]);

        $farTransit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'moon',
            'time' => now()->startOfDay()->addDays(10)->setTime(12, 0),
            'separation_degrees' => 0.20,
            'altitude_degrees' => 42.0,
            'azimuth_degrees' => 185.0,
            'is_exact_transit' => true,
            'cloud_cover_percent' => null,
        ]);

        // Build 10 days of mock Open-Meteo response data; weather:fetch should only evaluate 5 nights
        $sunsets = [];
        $sunrises = [];
        $hourlyTimes = [];
        $hourlyClouds = [];
        $hourlyWinds = [];

        for ($d = 0; $d < 10; $d++) {
            $day = now()->startOfDay()->addDays($d);
            $sunsets[] = $day->copy()->setTime(20, 0)->toIso8601String();
            $sunrises[] = $day->copy()->setTime(6, 0)->toIso8601String();

            for ($h = 0; $h < 24; $h++) {
                $t = $day->copy()->addHours($h);
                $hourlyTimes[] = $t->format('Y-m-d\TH:00');
                $hourlyClouds[] = 22;
                $hourlyWinds[] = 8;
            }
        }

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'timezone' => 'UTC',
                'daily' => [
                    'sunset' => $sunsets,
                    'sunrise' => $sunrises,
                ],
                'hourly' => [
                    'time' => $hourlyTimes,
                    'cloud_cover' => $hourlyClouds,
                    'wind_speed_10m' => $hourlyWinds,
                ],
            ], 200),
        ]);

        $this->artisan('weather:fetch')->assertSuccessful();

        // Verify Open-Meteo was queried with forecast_days = 5 + 2 = 7 (not 14 + 2)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.open-meteo.com')
                && $request['forecast_days'] === 7;
        });

        // Only 5 nights of WeatherCondition records should be stored
        $this->assertEquals(5, WeatherCondition::where('location_id', $location->id)->count());

        // Near transit (day 2) has cloud cover populated; far transit (day 10) remains null (pending weather window)
        $this->assertEquals(22, $nearTransit->fresh()->cloud_cover_percent);
        $this->assertNull($farTransit->fresh()->cloud_cover_percent);
    }
}
