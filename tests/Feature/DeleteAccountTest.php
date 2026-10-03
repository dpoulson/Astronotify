<?php

namespace Tests\Feature;

use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\User;
use App\Models\WeatherCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Features;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accounts_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        Livewire::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_before_account_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        Livewire::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }

    public function test_deleting_account_removes_all_locations_weather_transits_and_sessions(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $this->actingAs($user = User::factory()->create());

        $location = Location::create([
            'user_id' => $user->id,
            'name' => 'Home Observatory',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'elevation' => 25,
            'bortle' => 5,
        ]);

        $weather = WeatherCondition::create([
            'location_id' => $location->id,
            'date' => now()->toDateString(),
            'is_optimal' => true,
        ]);

        $transit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => 'moon',
            'time' => now()->addHours(3),
            'separation_degrees' => 0.12,
            'altitude_degrees' => 45,
            'azimuth_degrees' => 180,
            'is_exact_transit' => true,
        ]);

        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'dummy',
            'last_activity' => time(),
        ]);

        Livewire::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
        $this->assertDatabaseMissing('weather_conditions', ['id' => $weather->id]);
        $this->assertDatabaseMissing('iss_transits', ['id' => $transit->id]);
        $this->assertDatabaseMissing('sessions', ['id' => 'test-session-id']);
    }

    public function test_google_oauth_user_without_password_can_delete_account(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        $user = User::create([
            'name' => 'Google User',
            'email' => 'googleuser@example.com',
            'google_id' => 'google-oauth-12345',
            'password' => null,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.delete-user-form')
            ->call('deleteUser');

        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
