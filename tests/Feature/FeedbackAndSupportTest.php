<?php

namespace Tests\Feature;

use App\Livewire\AdminFeedbackRequests;
use App\Livewire\AdminSettings;
use App\Livewire\FeedbackModal;
use App\Models\FeedbackSubmission;
use App\Models\Setting;
use App\Models\StargazingSpot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class FeedbackAndSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_spot_suggestion(): void
    {
        Http::fake();
        Setting::set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/test/123');

        Livewire::test(FeedbackModal::class)
            ->call('openModal', ['type' => 'spot_suggestion'])
            ->set('name', 'Galileo Galilei')
            ->set('email', 'galileo@example.com')
            ->set('title', 'La Palma Roque de los Muchachos')
            ->set('country', 'Spain')
            ->set('region', 'Canary Islands')
            ->set('latitude', '28.7636')
            ->set('longitude', '-17.8947')
            ->set('elevation', '2396')
            ->set('bortle_class', 1)
            ->set('dark_sky_status', 'Starlight Reserve')
            ->set('description', 'Incredible dark skies above the sea of clouds with world-class telescopes.')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('feedback_submissions', [
            'type' => 'spot_suggestion',
            'status' => 'pending',
            'name' => 'Galileo Galilei',
            'email' => 'galileo@example.com',
            'title' => 'La Palma Roque de los Muchachos',
        ]);

        $submission = FeedbackSubmission::where('title', 'La Palma Roque de los Muchachos')->first();
        $this->assertNotNull($submission);
        $this->assertEquals('Spain', $submission->meta['country']);
        $this->assertEquals(28.7636, $submission->meta['latitude']);
        $this->assertEquals(1, $submission->meta['bortle_class']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com/api/webhooks')
                && str_contains($request->body(), 'La Palma Roque de los Muchachos')
                && str_contains($request->body(), 'Spain');
        });
    }

    public function test_authenticated_user_can_submit_feature_request(): void
    {
        Http::fake();
        $user = User::factory()->create([
            'name' => 'Carl Sagan',
            'email' => 'carl@cosmos.org',
        ]);

        $this->actingAs($user);

        Livewire::test(FeedbackModal::class)
            ->call('openModal', ['type' => 'feature_request'])
            ->assertSet('name', 'Carl Sagan')
            ->assertSet('email', 'carl@cosmos.org')
            ->set('title', 'Telegram Alert Bot')
            ->set('description', 'Would love to receive transit alerts via a dedicated Telegram bot in addition to email.')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('feedback_submissions', [
            'user_id' => $user->id,
            'type' => 'feature_request',
            'title' => 'Telegram Alert Bot',
            'name' => 'Carl Sagan',
        ]);
    }

    public function test_honeypot_silently_rejects_bots(): void
    {
        Livewire::test(FeedbackModal::class)
            ->call('openModal')
            ->set('name', 'Bot spammer')
            ->set('email', 'spam@bot.net')
            ->set('title', 'Crypto promotion')
            ->set('description', 'Buy coins now at discount prices online.')
            ->set('website', 'https://spam-link.ru')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseCount('feedback_submissions', 0);
    }

    public function test_non_admin_cannot_access_admin_requests(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);
        $response = $this->get('/admin/requests');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_filter_requests(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        FeedbackSubmission::create([
            'type' => 'spot_suggestion',
            'status' => 'pending',
            'name' => 'Alice',
            'email' => 'alice@test.com',
            'title' => 'Galloway Forest Park',
            'description' => 'Dark sky park in Scotland.',
            'meta' => ['country' => 'United Kingdom', 'bortle_class' => 2],
        ]);

        FeedbackSubmission::create([
            'type' => 'bug_report',
            'status' => 'resolved',
            'name' => 'Bob',
            'email' => 'bob@test.com',
            'title' => 'Map rendering lag on Safari',
            'description' => 'Canvas tiles stutter on older iPad.',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/requests');
        $response->assertStatus(200);
        $response->assertSee('Galloway Forest Park');

        Livewire::test(AdminFeedbackRequests::class)
            ->set('statusFilter', 'resolved')
            ->assertSee('Map rendering lag on Safari')
            ->assertDontSee('Galloway Forest Park');
    }

    public function test_admin_can_promote_spot_suggestion_to_live_stargazing_spot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $submission = FeedbackSubmission::create([
            'type' => 'spot_suggestion',
            'status' => 'pending',
            'name' => 'Observatory Director',
            'email' => 'director@observatory.org',
            'title' => 'Teide High Altitude Observatory',
            'description' => 'Superb dark sky mountain ridge with high transparency.',
            'meta' => [
                'country' => 'Spain',
                'region' => 'Tenerife',
                'latitude' => 28.3005,
                'longitude' => -16.5105,
                'elevation' => 2390,
                'bortle_class' => 2,
                'dark_sky_status' => 'Starlight Tourist Destination',
            ],
        ]);

        Livewire::test(AdminFeedbackRequests::class)
            ->call('promoteToSpot', $submission->id)
            ->assertSee('Successfully promoted');

        // Check StargazingSpot created
        $this->assertDatabaseHas('stargazing_spots', [
            'name' => 'Teide High Altitude Observatory',
            'country' => 'Spain',
            'region' => 'Tenerife',
            'bortle_class' => 2,
            'is_active' => true,
        ]);

        // Check submission status updated
        $submission->refresh();
        $this->assertEquals('approved', $submission->status);
        $this->assertNotNull($submission->resolved_at);
        $this->assertStringContainsString('Promoted to StargazingSpot', $submission->admin_notes);
    }

    public function test_admin_settings_can_save_and_test_discord_webhook(): void
    {
        Http::fake([
            'discord.com/*' => Http::response([], 204),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(AdminSettings::class)
            ->set('discord_feedback_webhook_url', 'https://discord.com/api/webhooks/999/xyz')
            ->set('discord_invite_url', 'https://discord.gg/UuwaXjRjZU')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Global settings successfully updated.');

        $this->assertEquals('https://discord.com/api/webhooks/999/xyz', Setting::get('discord_feedback_webhook_url'));
        $this->assertEquals('https://discord.gg/UuwaXjRjZU', Setting::get('discord_invite_url'));

        Livewire::test(AdminSettings::class)
            ->call('testDiscordWebhook')
            ->assertSee('Discord test notification successfully delivered!');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com/api/webhooks/999/xyz')
                && str_contains($request->body(), 'Discord Webhook Test Successful');
        });
    }
}
