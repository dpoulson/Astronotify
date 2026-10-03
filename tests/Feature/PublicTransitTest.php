<?php

namespace Tests\Feature;

use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTransitTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_transit_page_renders_with_valid_token(): void
    {
        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Backyard Observatory',
            'latitude' => 54.0,
            'longitude' => -2.8,
            'elevation' => 50,
            'is_active' => true,
        ]);

        $transit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'moon',
            'time' => now()->addDays(2),
            'separation_degrees' => 0.12,
            'altitude_degrees' => 45.0,
            'azimuth_degrees' => 180.0,
            'is_exact_transit' => true,
            'cloud_cover_percent' => 15,
        ]);

        $this->assertNotNull($transit->public_token);

        $response = $this->get("/transit/{$transit->public_token}");

        $response->assertStatus(200);
        $response->assertSee('ISS Lunar Transit over Backyard Observatory');
        $response->assertSee('0.12°');
        $response->assertSee('45°');
        $response->assertSee('15%');
        $response->assertSee('Google Calendar');
        $response->assertSee('Download iCal');
    }

    public function test_invalid_transit_token_returns_404(): void
    {
        $response = $this->get('/transit/invalid-token-12345');
        $response->assertStatus(404);
    }
}
