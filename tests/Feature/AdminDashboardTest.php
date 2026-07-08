<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_trigger_weather_fetch(): void
    {
        // Mock CelesTrak and Open-Meteo
        \Illuminate\Support\Facades\Http::fake([
            'api.open-meteo.com/*' => \Illuminate\Support\Facades\Http::response([
                'timezone' => 'UTC',
                'daily' => [
                    'sunset' => [today()->addDay()->format('Y-m-d\TH:i')],
                    'sunrise' => [today()->addDay()->format('Y-m-d\TH:i'), today()->addDays(2)->format('Y-m-d\TH:i')],
                ],
                'hourly' => [
                    'time' => [],
                    'cloud_cover' => [],
                    'wind_speed_10m' => [],
                ]
            ], 200),
            'celestrak.org/*' => \Illuminate\Support\Facades\Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200)
        ]);

        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        Livewire::test(\App\Livewire\AdminDashboard::class)
            ->call('triggerWeatherFetch')
            ->assertSet('sysMessageType', 'success')
            ->assertSet('sysMessage', 'Weather forecast data fetched and processed successfully!');
    }

    public function test_admin_can_trigger_transit_calculations(): void
    {
        // Mock CelesTrak
        \Illuminate\Support\Facades\Http::fake([
            'celestrak.org/*' => \Illuminate\Support\Facades\Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200)
        ]);

        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        Livewire::test(\App\Livewire\AdminDashboard::class)
            ->call('triggerTransitCalculation')
            ->assertSet('sysMessageType', 'success')
            ->assertSet('sysMessage', 'ISS orbital transits calculated successfully!');
    }
}
