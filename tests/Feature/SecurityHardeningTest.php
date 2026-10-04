<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use App\Livewire\AdminDashboard;
use App\Livewire\AdminLocationsList;
use App\Livewire\AdminUsersList;
use App\Livewire\FeedbackModal;
use App\Livewire\LocationManager;
use App\Models\Location;
use App\Models\User;
use App\Services\DiscordWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_mount_or_call_admin_livewire_components(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($regularUser);

        Livewire::test(AdminUsersList::class)->assertForbidden();
    }

    public function test_non_admin_cannot_mount_admin_locations_list(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($regularUser);

        Livewire::test(AdminLocationsList::class)->assertForbidden();
    }

    public function test_admin_can_mount_admin_components(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(AdminUsersList::class)->assertStatus(200);
        Livewire::test(AdminLocationsList::class)->assertStatus(200);
        Livewire::test(AdminDashboard::class)->assertStatus(200);
    }

    public function test_is_admin_is_not_mass_assignable_on_user_model(): void
    {
        $user = new User([
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'password' => 'secret123',
            'is_admin' => true,
        ]);

        $this->assertFalse((bool) $user->is_admin);
    }

    public function test_admin_cannot_accidentally_delete_self_or_root_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(AdminUsersList::class)
            ->call('deleteUser', $admin->id);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_regular_user_cannot_exceed_ten_locations(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        // Pre-create 10 locations
        for ($i = 1; $i <= 10; $i++) {
            Location::create([
                'user_id' => $user->id,
                'name' => "Observatory {$i}",
                'latitude' => 52.0 + ($i * 0.1),
                'longitude' => -1.0,
                'elevation' => 50,
            ]);
        }

        Livewire::test(LocationManager::class)
            ->set('name', 'Eleventh Location')
            ->set('latitude', 52.0)
            ->set('longitude', -1.0)
            ->set('elevation', 50)
            ->set('min_night_length_hours', 4)
            ->set('min_clear_hours', 2)
            ->set('max_wind_speed', 20.0)
            ->set('max_cloud_cover', 20)
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertCount(10, $user->locations()->get());
    }

    public function test_security_headers_middleware_attaches_defensive_headers(): void
    {
        $middleware = new SecurityHeaders;
        $request = Request::create('/', 'GET');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertEquals('camera=(), microphone=()', $response->headers->get('Permissions-Policy'));
    }

    public function test_feedback_submission_is_rate_limited(): void
    {
        RateLimiter::clear('feedback:127.0.0.1');

        $mockService = $this->createMock(DiscordWebhookService::class);
        $mockService->method('sendFeedbackNotification')->willReturn(true);

        $component = Livewire::test(FeedbackModal::class)
            ->set('name', 'Tester')
            ->set('email', 'test@example.com')
            ->set('type', 'feature_request')
            ->set('title', 'New Feature')
            ->set('description', 'Detailed feature request explanation');

        // Submit 5 times (allowed)
        for ($i = 0; $i < 5; $i++) {
            $component->call('submit', $mockService);
            $component->set('submitted', false);
        }

        // 6th submission should be rate-limited
        $component->call('submit', $mockService)
            ->assertHasErrors(['title']);
    }
}
