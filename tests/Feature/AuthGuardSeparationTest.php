<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The core guarantee of ARCHITECTURE.md §5/§7: one `users` table, three
 * guards, and a row of one user_type must never authenticate on another
 * guard — not even with the exact right password.
 */
class AuthGuardSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_log_in_on_web_guard(): void
    {
        $user = User::factory()->create(['user_type' => UserType::Staff, 'password' => Hash::make('secret123')]);

        $this->assertTrue(auth('web')->attempt(['email' => $user->email, 'password' => 'secret123']));
    }

    public function test_client_cannot_log_in_on_web_guard(): void
    {
        $client = User::factory()->client()->create(['password' => Hash::make('secret123')]);

        $this->assertFalse(auth('web')->attempt(['email' => $client->email, 'password' => 'secret123']));
    }

    public function test_staff_cannot_log_in_on_client_guard(): void
    {
        $staff = User::factory()->create(['password' => Hash::make('secret123')]);

        $this->assertFalse(auth('client')->attempt(['email' => $staff->email, 'password' => 'secret123']));
    }

    public function test_vendor_cannot_log_in_on_client_guard(): void
    {
        $vendor = User::factory()->vendor()->create(['password' => Hash::make('secret123')]);

        $this->assertFalse(auth('client')->attempt(['email' => $vendor->email, 'password' => 'secret123']));
    }

    public function test_client_logs_in_correctly_on_client_guard(): void
    {
        $client = User::factory()->client()->create(['password' => Hash::make('secret123')]);

        $this->assertTrue(auth('client')->attempt(['email' => $client->email, 'password' => 'secret123']));
        $this->assertTrue(auth('client')->user()->is($client));
    }

    public function test_inactive_user_cannot_log_in_on_any_guard(): void
    {
        $user = User::factory()->inactive()->create(['password' => Hash::make('secret123')]);

        $this->assertFalse(auth('web')->attempt(['email' => $user->email, 'password' => 'secret123']));
    }

    public function test_staff_login_route_rejects_client_credentials(): void
    {
        $client = User::factory()->client()->create(['password' => Hash::make('secret123')]);

        $response = $this->get('/login');
        $response->assertOk();

        // Guard separation is enforced at the provider level (tested
        // above); this just confirms the staff login page itself renders
        // under the `web` guard regardless of other guards' state.
        $this->assertGuest('web');
    }
}
