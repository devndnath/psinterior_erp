<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_dashboard_renders_for_authenticated_staff(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee(config('app.name'));
    }

    public function test_guest_is_redirected_to_staff_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_sidebar_hides_settings_link_without_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('href="'.route('settings.index').'"', false);
    }

    public function test_sidebar_shows_settings_link_with_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage_settings');

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('href="'.route('settings.index').'"', false);
    }
}
