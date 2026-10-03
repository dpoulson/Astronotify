<?php

namespace Tests\Feature;

use App\Models\StargazingSpot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminStargazingSpotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_spots(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/admin/spots');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_spots(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $response = $this->actingAs($admin)->get('/admin/spots');
        $response->assertStatus(200);
        $response->assertSee('Curated Stargazing Spots');
    }

    public function test_admin_can_create_new_stargazing_spot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(\App\Livewire\AdminStargazingSpots::class)
            ->set('name', 'Elan Valley Dark Sky Park')
            ->set('country', 'United Kingdom')
            ->set('region', 'Mid Wales')
            ->set('latitude', '52.2667')
            ->set('longitude', '-3.6167')
            ->set('elevation', 380)
            ->set('bortle_class', 2)
            ->set('dark_sky_status', 'IDA Dark Sky Park')
            ->set('description', 'Spectacular dark Victorian reservoir park.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stargazing_spots', [
            'name' => 'Elan Valley Dark Sky Park',
            'slug' => 'elan-valley-dark-sky-park',
            'country' => 'United Kingdom',
            'bortle_class' => 2,
        ]);
    }

    public function test_admin_can_edit_and_toggle_spot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $spot = StargazingSpot::create([
            'name' => 'Original Spot',
            'slug' => 'original-spot',
            'country' => 'Canada',
            'latitude' => 50.0,
            'longitude' => -110.0,
            'elevation' => 500,
            'bortle_class' => 2,
            'is_active' => true,
        ]);

        Livewire::test(\App\Livewire\AdminStargazingSpots::class)
            ->call('editSpot', $spot->id)
            ->set('name', 'Updated Spot Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stargazing_spots', [
            'id' => $spot->id,
            'name' => 'Updated Spot Name',
        ]);

        Livewire::test(\App\Livewire\AdminStargazingSpots::class)
            ->call('toggleActive', $spot->id);

        $this->assertFalse($spot->fresh()->is_active);
    }

    public function test_admin_can_delete_spot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $spot = StargazingSpot::create([
            'name' => 'To Be Deleted',
            'slug' => 'to-be-deleted',
            'country' => 'Spain',
            'latitude' => 40.0,
            'longitude' => -3.0,
            'elevation' => 600,
            'bortle_class' => 3,
            'is_active' => true,
        ]);

        Livewire::test(\App\Livewire\AdminStargazingSpots::class)
            ->call('deleteSpot', $spot->id);

        $this->assertDatabaseMissing('stargazing_spots', [
            'id' => $spot->id,
        ]);
    }
}
