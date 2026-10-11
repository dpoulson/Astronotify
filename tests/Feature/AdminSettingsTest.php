<?php

namespace Tests\Feature;

use App\Livewire\AdminSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_settings(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get('/admin/settings');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_settings(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $response = $this->get('/admin/settings');
        $response->assertStatus(200);
    }

    public function test_admin_can_save_settings_successfully(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        Livewire::test(AdminSettings::class)
            ->set('forecast_days', 10)
            ->set('grouping_decimal_places', 2)
            ->set('conjunction_threshold', 1.25)
            ->set('transit_min_altitude', 8.5)
            ->set('max_locations_per_user', 15)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(10, Setting::where('key', 'forecast_days')->value('value'));
        $this->assertEquals(2, Setting::where('key', 'grouping_decimal_places')->value('value'));
        $this->assertEquals(1.25, Setting::where('key', 'conjunction_threshold')->value('value'));
        $this->assertEquals(8.5, Setting::where('key', 'transit_min_altitude')->value('value'));
        $this->assertEquals(15, Setting::where('key', 'max_locations_per_user')->value('value'));
    }

    public function test_admin_settings_validation_errors(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        Livewire::test(AdminSettings::class)
            ->set('forecast_days', 20) // max is 16
            ->set('grouping_decimal_places', 5) // max is 4
            ->set('conjunction_threshold', 0) // min is 0.01
            ->set('transit_min_altitude', -1) // min is 0
            ->set('max_locations_per_user', 0) // min is 1
            ->call('save')
            ->assertHasErrors([
                'forecast_days' => 'max',
                'grouping_decimal_places' => 'max',
                'conjunction_threshold' => 'min',
                'transit_min_altitude' => 'min',
                'max_locations_per_user' => 'min',
            ]);
    }
}
