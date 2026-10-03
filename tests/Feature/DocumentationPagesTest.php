<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Never Miss a Flawless Night Sky');
        $response->assertSee('ISS Solar &amp; Lunar Transits', false);
    }

    public function test_about_page_accessible_to_guest_and_user(): void
    {
        // Guest
        $response = $this->get('/about');
        $response->assertStatus(200);
        $response->assertSee('What is Astronotify?');

        // Authenticated user
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/about');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('What is Astronotify?');
    }

    public function test_faq_page_accessible_to_guest_and_user(): void
    {
        // Guest
        $response = $this->get('/faq');
        $response->assertStatus(200);
        $response->assertSee('Help &amp; Frequently Asked Questions', false);
        $response->assertSee('Separation Degrees');
        $response->assertSee('Critical Safety Warning for Solar Transits');

        // Authenticated user
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/faq');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Help &amp; Frequently Asked Questions', false);
    }

    public function test_help_route_redirects_to_faq(): void
    {
        $response = $this->get('/help');
        $response->assertRedirect('/faq');
    }
}
