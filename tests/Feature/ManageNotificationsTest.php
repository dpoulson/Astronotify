<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class ManageNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_url_aborts_with_401(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('notifications.manage', ['user' => $user->id]));
        $response->assertStatus(401);
    }

    public function test_signed_url_renders_successfully(): void
    {
        $user = User::factory()->create();
        $signedUrl = URL::signedRoute('notifications.manage', ['user' => $user->id]);

        $response = $this->get($signedUrl);
        $response->assertStatus(200);
    }

    public function test_user_can_save_preferences_using_signed_url(): void
    {
        $user = User::factory()->create();
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Field Observatory',
            'latitude' => 54.0,
            'longitude' => -2.8,
            'elevation' => 100,
            'notify_stargazing_alerts' => true,
            'notify_iss_sun_transit' => true,
            'notify_iss_moon_transit' => true,
            'is_active' => true,
        ]);

        $signedUrl = URL::signedRoute('notifications.manage', ['user' => $user->id]);

        // Parse query params to mock active signed route for Livewire mount check
        $queryParams = [];
        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

        $user->load('locations');

        Livewire::withQueryParams($queryParams)
            ->test(\App\Livewire\ManageNotifications::class, ['user' => $user])
            ->set("preferences.{$location->id}.notify_stargazing_alerts", false)
            ->set("preferences.{$location->id}.notify_iss_sun_transit", false)
            ->set("preferences.{$location->id}.notify_iss_moon_transit", false)
            ->call('save')
            ->assertSet('saved', true);

        $location->refresh();
        $this->assertFalse($location->notify_stargazing_alerts);
        $this->assertFalse($location->notify_iss_sun_transit);
        $this->assertFalse($location->notify_iss_moon_transit);
    }
}
