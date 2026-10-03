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

    public function test_user_can_track_spot_in_account(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'celestrak.org/*' => \Illuminate\Support\Facades\Http::response("ISS (ZARYA)\n1 25544U 98067A   26188.50835634  .00005806  00000+0  11369-3 0  9990\n2 25544  51.6304 199.5144 0006687 267.6545  92.3678 15.48933372574901", 200),
            'api.open-meteo.com/*' => \Illuminate\Support\Facades\Http::response(['hourly' => ['time' => []], 'daily' => ['time' => []]], 200),
        ]);

        $user = User::factory()->create();
        $spot = StargazingSpot::create([
            'name' => 'Brecon Beacons',
            'slug' => 'brecon-beacons',
            'country' => 'United Kingdom',
            'latitude' => 51.88,
            'longitude' => -3.43,
            'elevation' => 290,
            'bortle_class' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\PublicSpotView::class, ['slug' => $spot->slug])
            ->call('trackSpotInAccount')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('locations', [
            'user_id' => $user->id,
            'name' => 'Brecon Beacons',
            'latitude' => 51.88,
            'longitude' => -3.43,
        ]);
    }
}
