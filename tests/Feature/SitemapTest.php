<?php

namespace Tests\Feature;

use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\StargazingSpot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_renders_valid_xml_with_core_routes(): void
    {
        Cache::flush();

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee('<urlset', false);
        $response->assertSee(url('/'), false);
        $response->assertSee(route('spots.index'), false);
        $response->assertSee(route('about'), false);
        $response->assertSee(route('faq'), false);
        $response->assertSee(route('privacy'), false);
    }

    public function test_sitemap_includes_active_spots_and_excludes_inactive_spots(): void
    {
        Cache::flush();

        $activeSpot = StargazingSpot::create([
            'name' => 'Active Dark Sky Spot',
            'slug' => 'active-dark-sky-spot',
            'country' => 'United Kingdom',
            'latitude' => 55.0,
            'longitude' => -3.0,
            'bortle_class' => 2,
            'is_active' => true,
        ]);

        $inactiveSpot = StargazingSpot::create([
            'name' => 'Inactive Spot',
            'slug' => 'inactive-spot',
            'country' => 'United Kingdom',
            'latitude' => 51.0,
            'longitude' => -0.1,
            'bortle_class' => 7,
            'is_active' => false,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee(route('spots.show', $activeSpot->slug), false);
        $response->assertDontSee(route('spots.show', $inactiveSpot->slug), false);
    }

    public function test_sitemap_includes_upcoming_transits(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Home Observatory',
            'latitude' => 52.5,
            'longitude' => -1.2,
        ]);

        $upcomingTransit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'moon',
            'time' => now()->addDays(2),
            'separation_degrees' => 0.15,
            'altitude_degrees' => 45.0,
            'azimuth_degrees' => 180.0,
            'is_exact_transit' => true,
            'public_token' => 'testtransit12345',
        ]);

        $pastTransit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'sun',
            'time' => now()->subDays(2),
            'separation_degrees' => 0.20,
            'altitude_degrees' => 30.0,
            'azimuth_degrees' => 140.0,
            'is_exact_transit' => true,
            'public_token' => 'pasttransit12345',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee(route('transit.show', $upcomingTransit->public_token), false);
        $response->assertDontSee(route('transit.show', $pastTransit->public_token), false);
    }
}
