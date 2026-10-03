<?php

namespace Tests\Feature;

use App\Models\StargazingSpot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSpotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_spots_directory_page_is_publicly_accessible(): void
    {
        StargazingSpot::create([
            'name' => 'Kielder Observatory',
            'slug' => 'kielder-observatory',
            'country' => 'United Kingdom',
            'region' => 'Northumberland',
            'latitude' => 55.2333,
            'longitude' => -2.5833,
            'elevation' => 360,
            'bortle_class' => 2,
            'dark_sky_status' => 'IDA Gold Tier Dark Sky Park',
            'description' => 'Dark sky discovery site in England.',
            'is_active' => true,
        ]);

        $response = $this->get('/spots');

        $response->assertStatus(200);
        $response->assertSee('Kielder Observatory');
        $response->assertSee('Northumberland');
        $response->assertSee('Bortle 2');
    }

    public function test_spots_can_be_filtered_by_search_term(): void
    {
        StargazingSpot::create([
            'name' => 'Galloway Forest Park',
            'slug' => 'galloway-forest-park',
            'country' => 'United Kingdom',
            'region' => 'Scotland',
            'latitude' => 55.08,
            'longitude' => -4.41,
            'elevation' => 280,
            'bortle_class' => 2,
            'is_active' => true,
        ]);

        StargazingSpot::create([
            'name' => 'Cherry Springs State Park',
            'slug' => 'cherry-springs',
            'country' => 'United States',
            'region' => 'Pennsylvania',
            'latitude' => 41.66,
            'longitude' => -77.82,
            'elevation' => 700,
            'bortle_class' => 2,
            'is_active' => true,
        ]);

        Livewire::test(\App\Livewire\PublicSpotsList::class)
            ->set('search', 'Cherry')
            ->assertSee('Cherry Springs State Park')
            ->assertDontSee('Galloway Forest Park');
    }

    public function test_spot_detail_page_renders_successfully(): void
    {
        $spot = StargazingSpot::create([
            'name' => 'Pic du Midi',
            'slug' => 'pic-du-midi',
            'country' => 'France',
            'region' => 'Pyrenees',
            'latitude' => 42.93,
            'longitude' => 0.14,
            'elevation' => 2877,
            'bortle_class' => 2,
            'dark_sky_status' => 'IDA International Dark Sky Reserve',
            'description' => 'High mountain observatory.',
            'is_active' => true,
        ]);

        $response = $this->get("/spots/{$spot->slug}");

        $response->assertStatus(200);
        $response->assertSee('Pic du Midi');
        $response->assertSee('2877 m');
        $response->assertSee('Pyrenees');
    }

    public function test_inactive_spot_returns_404(): void
    {
        $spot = StargazingSpot::create([
            'name' => 'Secret Spot',
            'slug' => 'secret-spot',
            'country' => 'United Kingdom',
            'latitude' => 51.0,
            'longitude' => 0.0,
            'elevation' => 10,
            'bortle_class' => 3,
            'is_active' => false,
        ]);

        $response = $this->get("/spots/{$spot->slug}");

        $response->assertStatus(404);
    }
}
