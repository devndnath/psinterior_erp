<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Confirms permission gating actually blocks (spec §3: "Do NOT hard-code
 * permissions inside controllers; use middleware/policies/gates") and that
 * Super Admin's single, central bypass (AuthServiceProvider::boot())
 * really does bypass everything.
 */
class PermissionGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_without_permission_is_forbidden_from_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->actingAs($user)->get('/settings')->assertForbidden();
    }

    public function test_user_with_permission_can_access_settings(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage_settings');

        $this->actingAs($user)->get('/settings')->assertOk();
    }

    public function test_super_admin_bypasses_every_permission_check(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        // Deliberately does NOT hold manage_settings directly — the
        // Gate::before bypass must grant it anyway.
        $this->assertFalse($admin->hasPermissionTo('manage_settings'));
        $this->actingAs($admin)->get('/settings')->assertOk();
    }

    public function test_fourteen_roles_concept_maps_to_eleven_spatie_roles(): void
    {
        // Client, Vendor and Subcontractor are intentionally excluded from
        // Spatie roles (ARCHITECTURE.md / RolePermissionSeeder doc-block)
        // — their access is ownership-based via Policies, not permissions.
        $expected = [
            'super-admin', 'admin', 'project-manager', 'designer', 'site-engineer',
            'site-supervisor', 'accountant', 'procurement-officer', 'store-manager',
            'hr-manager', 'employee',
        ];

        $this->assertSame(
            11,
            \Spatie\Permission\Models\Role::where('guard_name', 'web')->count()
        );

        foreach ($expected as $role) {
            $this->assertTrue(
                \Spatie\Permission\Models\Role::where('name', $role)->exists(),
                "Expected role [{$role}] to be seeded."
            );
        }
    }

    public function test_accountant_can_approve_invoice_but_not_manage_employees(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('accountant');

        $this->assertTrue($accountant->can('approve_invoice'));
        $this->assertFalse($accountant->can('manage_employees'));
    }
}
