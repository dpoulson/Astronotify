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

    public function test_privacy_page_accessible_to_guest_and_user(): void
    {
        // Guest
        $response = $this->get('/privacy');
        $response->assertStatus(200);
        $response->assertSee('Privacy Policy &amp; Data Protection', false);
        $response->assertSee('Zero Tracking &amp; No Harvesting', false);
        $response->assertSee('Account Deletion &amp; Immediate Data Purge', false);

        // Alias route /privacy-policy
        $aliasResponse = $this->get('/privacy-policy');
        $aliasResponse->assertRedirect('/privacy');

        // Authenticated user
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/privacy');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Privacy Policy &amp; Data Protection', false);
    }

    public function test_faq_contains_privacy_and_data_control_tab(): void
    {
        $response = $this->get('/faq');
        $response->assertStatus(200);
        $response->assertSee('Privacy &amp; Data Control', false);
        $response->assertSee('Zero Tracking &amp; No Data Harvesting', false);
    }

    public function test_welcome_page_highlights_privacy_guarantees(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Privacy by Design');
        $response->assertSee('Built for Stargazers, Not Data Brokers');
    }

    public function test_welcome_page_has_open_graph_and_structured_data_tags(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('images/og-card.png', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('summary_large_image', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('WebApplication', false);
        $this->assertFileExists(public_path('images/og-card.png'));
    }
}
