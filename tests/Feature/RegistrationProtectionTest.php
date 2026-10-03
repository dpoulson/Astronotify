<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered_with_honeypot_fields(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('name="website_url"', false);
        $response->assertSee('name="form_time"', false);
    }

    public function test_bot_registration_with_honeypot_filled_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Spam Bot',
            'email' => 'spambot@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'website_url' => 'http://spam-link.ru',
            'form_time' => encrypt(microtime(true) - 5),
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['email' => 'spambot@example.com']);
    }

    public function test_bot_registration_submitted_too_fast_is_rejected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Speed Bot',
            'email' => 'speedbot@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'website_url' => '',
            'form_time' => encrypt(microtime(true) - 0.2), // only 200ms elapsed
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['email' => 'speedbot@example.com']);
    }

    public function test_valid_registration_creates_unverified_user_and_sends_verification(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Real User',
            'email' => 'realuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'website_url' => '',
            'form_time' => encrypt(microtime(true) - 3.0), // 3s elapsed
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'realuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        // Verification email should be sent
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);

        // Accessing dashboard should redirect to verification notice
        $dashResponse = $this->actingAs($user)->get('/dashboard');
        $dashResponse->assertRedirect('/email/verify');
    }

    public function test_verified_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
