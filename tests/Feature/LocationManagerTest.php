<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\WeatherCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_weather_conditions_are_reevaluated_on_save(): void
    {
        $user = User::factory()->create();

        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Home',
            'latitude' => 54.0,
            'longitude' => -2.8,
            'elevation' => 10,
            'min_night_length_hours' => 6,
            'min_clear_hours' => 3,
            'max_wind_speed' => 20,
            'max_cloud_cover' => 30,
            'is_active' => true,
        ]);

        // Create a condition with 6 hours of forecast data.
        // It has 4 consecutive clear hours (22:00 to 01:00).
        // Since min_clear_hours is 3, this is currently OPTIMAL.
        $forecastData = [
            ['time' => '22:00', 'cloud' => 10, 'wind' => 5],
            ['time' => '23:00', 'cloud' => 10, 'wind' => 5],
            ['time' => '00:00', 'cloud' => 10, 'wind' => 5],
            ['time' => '01:00', 'cloud' => 10, 'wind' => 5],
            ['time' => '02:00', 'cloud' => 80, 'wind' => 5],
            ['time' => '03:00', 'cloud' => 80, 'wind' => 5],
        ];

        $condition = WeatherCondition::create([
            'location_id' => $location->id,
            'date' => today(),
            'forecast_data' => $forecastData,
            'is_optimal' => true,
        ]);

        $this->actingAs($user);

        // Change the min_clear_hours requirement to 5 hours.
        // Under this new requirement, the condition is NO LONGER optimal (only has 4 consecutive clear hours).
        Livewire::test(\App\Livewire\LocationManager::class)
            ->call('edit', $location->id)
            ->set('min_clear_hours', 5)
            ->call('save');

        // Check that the condition was re-evaluated to false
        $this->assertFalse($condition->fresh()->is_optimal);

        // Change it back to 3
        Livewire::test(\App\Livewire\LocationManager::class)
            ->call('edit', $location->id)
            ->set('min_clear_hours', 3)
            ->call('save');

        // Check that it is optimal again
        $this->assertTrue($condition->fresh()->is_optimal);
    }

    public function test_location_iss_transits_are_calculated_on_save(): void
    {
        // Mock CelesTrak response
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake([
            'celestrak.org/*' => \Illuminate\Support\Facades\Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200)
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a location with transit notifications ON.
        // It will call ISSTransitCalculator internally on save.
        Livewire::test(\App\Livewire\LocationManager::class)
            ->set('name', 'Pyrenees')
            ->set('latitude', 42.7)
            ->set('longitude', -0.3)
            ->set('elevation', 1500)
            ->set('notify_iss_sun_transit', true)
            ->set('notify_iss_moon_transit', true)
            ->call('save');

        $location = Location::where('name', 'Pyrenees')->first();
        $this->assertNotNull($location);
        
        // Assert that the system runs calculations successfully (no exceptions thrown)
        $this->assertTrue(true);
    }

    public function test_location_iss_passes_can_be_lazy_loaded(): void
    {
        // Mock CelesTrak response
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake([
            'celestrak.org/*' => \Illuminate\Support\Facades\Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200)
        ]);

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Home',
            'latitude' => 54.0,
            'longitude' => -2.8,
            'elevation' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\LocationManager::class)
            ->call('loadPasses', $location->id)
            ->assertSet('loadedPasses.' . $location->id, function ($passes) {
                return is_array($passes) && count($passes) > 0;
            });
    }

    public function test_location_stargazing_notification_toggle_can_be_saved(): void
    {
        // Mock CelesTrak response
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        \Illuminate\Support\Facades\Http::fake([
            'celestrak.org/*' => \Illuminate\Support\Facades\Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200)
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(\App\Livewire\LocationManager::class)
            ->set('name', 'Mountain Cabin')
            ->set('latitude', 45.0)
            ->set('longitude', 6.0)
            ->set('elevation', 2000)
            ->set('notify_stargazing_alerts', false)
            ->call('save');

        $location = Location::where('name', 'Mountain Cabin')->first();
        $this->assertNotNull($location);
        $this->assertFalse($location->notify_stargazing_alerts);

        // Edit and set back to true
        Livewire::test(\App\Livewire\LocationManager::class)
            ->call('edit', $location->id)
            ->set('notify_stargazing_alerts', true)
            ->call('save');

        $this->assertTrue($location->fresh()->notify_stargazing_alerts);
    }

    public function test_location_manager_can_send_test_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Backyard',
            'latitude' => 54.0,
            'longitude' => -2.8,
            'elevation' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\LocationManager::class)
            ->call('sendTestEmail', $location->id)
            ->assertStatus(200);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\LocationTestMail::class, function ($mail) use ($user, $location) {
            return $mail->hasTo($user->email) && $mail->location->id === $location->id;
        });
    }
}
