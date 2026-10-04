<?php

namespace Tests\Feature;

use App\Livewire\LocationManager;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;
use Tests\TestCase;

class DiscordWebhookEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_discord_webhook_fires_when_user_registers(): void
    {
        Http::fake([
            'discord.com/*' => Http::response(null, 204),
        ]);

        Setting::set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/test/channel');

        $response = $this->post('/register', [
            'name' => 'Galileo Galilei',
            'email' => 'galileo@observatory.it',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'website_url' => '',
            'form_time' => encrypt(microtime(true) - 3.0),
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'galileo@observatory.it',
            'name' => 'Galileo Galilei',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com/api/webhooks/test/channel')
                && str_contains($request->body(), 'New Astronomer Registered')
                && str_contains($request->body(), 'Galileo Galilei')
                && str_contains($request->body(), 'galileo@observatory.it');
        });
    }

    public function test_discord_webhook_fires_when_user_creates_location_via_location_manager(): void
    {
        Http::fake([
            'discord.com/*' => Http::response(null, 204),
            'api.open-meteo.com/*' => Http::response([
                'daily' => ['time' => [], 'sunrise' => [], 'sunset' => []],
                'hourly' => ['time' => [], 'cloud_cover' => [], 'wind_speed_10m' => []],
            ], 200),
        ]);

        Setting::set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/test/channel');

        $user = User::factory()->create([
            'name' => 'Johannes Kepler',
            'email' => 'kepler@planetary.org',
        ]);

        $this->actingAs($user);

        Livewire::test(LocationManager::class)
            ->set('name', 'Prague Castle Observatory')
            ->set('latitude', 50.0903)
            ->set('longitude', 14.4005)
            ->set('elevation', 280)
            ->set('bortle', 5)
            ->set('min_night_length_hours', 5)
            ->set('min_clear_hours', 3)
            ->set('max_wind_speed', 22.5)
            ->set('max_cloud_cover', 25)
            ->set('notify_iss_sun_transit', true)
            ->set('notify_iss_moon_transit', true)
            ->set('notify_stargazing_alerts', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('locations', [
            'user_id' => $user->id,
            'name' => 'Prague Castle Observatory',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com/api/webhooks/test/channel')
                && str_contains($request->body(), 'New Observing Location Added')
                && str_contains($request->body(), 'Prague Castle Observatory')
                && str_contains($request->body(), 'Johannes Kepler');
        });
    }

    public function test_discord_webhook_fires_when_location_created_directly(): void
    {
        Http::fake([
            'discord.com/*' => Http::response(null, 204),
        ]);

        Setting::set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/test/channel');

        $user = User::factory()->create([
            'name' => 'Edwin Hubble',
            'email' => 'hubble@wilson.org',
        ]);

        Location::create([
            'user_id' => $user->id,
            'name' => 'Mount Wilson Observatory',
            'latitude' => 34.2256,
            'longitude' => -118.0572,
            'elevation' => 1742,
            'bortle' => 4,
            'min_night_length_hours' => 6,
            'min_clear_hours' => 3,
            'max_wind_speed' => 20,
            'max_cloud_cover' => 15,
            'is_active' => true,
            'notify_stargazing_alerts' => true,
            'notify_iss_sun_transit' => false,
            'notify_iss_moon_transit' => true,
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com/api/webhooks/test/channel')
                && str_contains($request->body(), 'Mount Wilson Observatory')
                && str_contains($request->body(), 'Edwin Hubble')
                && str_contains($request->body(), '1742m elevation');
        });
    }

    public function test_discord_webhook_does_not_fire_when_url_is_empty(): void
    {
        Http::fake();

        // Webhook URL is deliberately empty / not configured
        Setting::set('discord_feedback_webhook_url', '');

        $user = User::factory()->create();

        Location::create([
            'user_id' => $user->id,
            'name' => 'Unconfigured Location',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'is_active' => true,
        ]);

        Http::assertNothingSent();
    }

    public function test_discord_webhook_failure_does_not_break_registration_or_location_creation(): void
    {
        Http::fake([
            'discord.com/*' => Http::response('Internal Server Error', 500),
        ]);

        Setting::set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/failing/channel');

        $user = User::factory()->create();

        // Creating a location should still succeed cleanly despite Discord 500
        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Resilient Spot',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Resilient Spot',
        ]);
    }

    public function test_discord_webhook_fires_when_user_registers_via_google_oauth(): void
    {
        Http::fake([
            'discord.com/*' => Http::response(null, 204),
        ]);

        Setting::set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/test/channel');

        $abstractUser = new SocialiteUser;
        $abstractUser->id = 'google-id-12345';
        $abstractUser->name = 'Ada Lovelace';
        $abstractUser->email = 'ada@lovelace.org';
        $abstractUser->token = 'mock-google-token';
        $abstractUser->refreshToken = 'mock-google-refresh-token';

        $provider = \Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'google_id' => 'google-id-12345',
            'email' => 'ada@lovelace.org',
            'name' => 'Ada Lovelace',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com/api/webhooks/test/channel')
                && str_contains($request->body(), 'Ada Lovelace')
                && str_contains($request->body(), 'ada@lovelace.org')
                && str_contains($request->body(), 'Google OAuth');
        });
    }
}
