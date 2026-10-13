<?php

namespace Tests\Feature;

use App\Livewire\Departments\Form as DepartmentForm;
use App\Livewire\Departments\Index as DepartmentIndex;
use App\Livewire\Designations\Form as DesignationForm;
use App\Livewire\Roles\Form as RoleForm;
use App\Livewire\Roles\Index as RoleIndex;
use App\Livewire\Users\Form as UserForm;
use App\Livewire\Users\Index as UserIndex;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the three audit gaps closed in this phase (the fourth,
 * Employee self-service, was explicitly left out of scope): User
 * management, Role & permission management, and Department/Designation
 * CRUD. Before this, the only staff login was SuperAdminSeeder's, roles
 * only existed as hand-edited PHP in RolePermissionSeeder, and
 * Department/Designation had models/migrations but no screen.
 */
class UserRoleDepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    // --- User management ---------------------------------------------------

    public function test_a_plain_staff_user_without_permission_cannot_see_the_user_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(UserIndex::class)
            ->assertForbidden();
    }

    public function test_an_admin_can_create_a_staff_user_and_assign_a_role(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(UserForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.test')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', ['site-engineer'])
            ->call('save')
            ->assertHasNoErrors();

        $created = User::staff()->where('email', 'jane@example.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('site-engineer'));
        $this->assertTrue($created->is_active);
    }

    public function test_an_admin_cannot_grant_the_super_admin_role(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(UserForm::class)
            ->set('name', 'Wannabe Admin')
            ->set('email', 'wannabe@example.test')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', ['super-admin'])
            ->call('save')
            ->assertHasErrors('roles');

        $this->assertNull(User::staff()->where('email', 'wannabe@example.test')->first());
    }

    public function test_a_super_admin_can_grant_the_super_admin_role(): void
    {
        $superAdmin = $this->superAdmin();

        Livewire::actingAs($superAdmin)->test(UserForm::class)
            ->set('name', 'Second Super Admin')
            ->set('email', 'second-super@example.test')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', ['super-admin'])
            ->call('save')
            ->assertHasNoErrors();

        $created = User::staff()->where('email', 'second-super@example.test')->first();
        $this->assertTrue($created->hasRole('super-admin'));
    }

    public function test_an_admin_cannot_edit_a_super_admins_account(): void
    {
        $admin = $this->admin();
        $superAdmin = $this->superAdmin();

        Livewire::actingAs($admin)->test(UserForm::class, ['user' => $superAdmin])
            ->assertForbidden();
    }

    public function test_a_user_cannot_delete_their_own_login(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(UserIndex::class)
            ->call('deleteUser', $admin->id)
            ->assertForbidden();

        $this->assertNotNull($admin->fresh());
    }

    public function test_updating_a_user_without_a_new_password_keeps_the_old_one(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['password' => bcrypt('original-password')]);

        Livewire::actingAs($admin)->test(UserForm::class, ['user' => $target])
            ->set('name', 'Renamed User')
            ->set('roles', [])
            ->call('save')
            ->assertHasNoErrors();

        $target->refresh();
        $this->assertSame('Renamed User', $target->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('original-password', $target->password));
    }

    // --- Role & permission management --------------------------------------

    public function test_a_plain_staff_user_without_permission_cannot_see_the_role_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(RoleIndex::class)
            ->assertForbidden();
    }

    public function test_an_admin_can_create_a_custom_role_with_chosen_permissions(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(RoleForm::class)
            ->set('name', 'junior-accountant')
            ->set('permissions', ['view_invoices', 'view_payments'])
            ->call('save')
            ->assertHasNoErrors();

        $role = Role::where('name', 'junior-accountant')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view_invoices'));
        $this->assertTrue($role->hasPermissionTo('view_payments'));
        $this->assertFalse($role->hasPermissionTo('delete_invoices'));
    }

    public function test_an_admin_can_adjust_an_existing_roles_permissions(): void
    {
        $admin = $this->admin();
        $role = Role::where('name', 'site-engineer')->first();

        Livewire::actingAs($admin)->test(RoleForm::class, ['role' => $role])
            ->set('permissions', ['view_tasks'])
            ->call('save')
            ->assertHasNoErrors();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('view_tasks'));
        $this->assertFalse($role->hasPermissionTo('create_tasks'));
    }

    public function test_the_super_admin_role_cannot_be_edited_or_deleted(): void
    {
        $admin = $this->admin();
        $superAdminRole = Role::where('name', 'super-admin')->first();

        Livewire::actingAs($admin)->test(RoleForm::class, ['role' => $superAdminRole])
            ->assertForbidden();

        Livewire::actingAs($admin)->test(RoleIndex::class)
            ->call('deleteRole', $superAdminRole->id)
            ->assertForbidden();
    }

    public function test_a_role_with_users_assigned_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $role = Role::where('name', 'designer')->first();
        User::factory()->create()->assignRole('designer');

        Livewire::actingAs($admin)->test(RoleIndex::class)
            ->call('deleteRole', $role->id)
            ->assertForbidden();

        $this->assertNotNull($role->fresh());
    }

    // --- Department & Designation CRUD --------------------------------------

    public function test_a_plain_staff_user_without_permission_cannot_see_the_department_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(DepartmentIndex::class)
            ->assertForbidden();
    }

    public function test_an_hr_manager_can_create_a_department_and_a_designation_under_it(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole('hr-manager');

        Livewire::actingAs($hr)->test(DepartmentForm::class)
            ->set('name', 'Site Operations')
            ->set('code', 'OPS')
            ->call('save')
            ->assertHasNoErrors();

        $department = Department::where('name', 'Site Operations')->first();
        $this->assertNotNull($department);

        Livewire::actingAs($hr)->test(DesignationForm::class)
            ->set('department_id', (string) $department->id)
            ->set('name', 'Site Supervisor')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNotNull(Designation::where('name', 'Site Supervisor')->where('department_id', $department->id)->first());
    }

    public function test_a_department_still_in_use_cannot_be_deleted(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole('hr-manager');
        $department = Department::factory()->create();
        Employee::factory()->create(['department_id' => $department->id]);

        Livewire::actingAs($hr)->test(DepartmentIndex::class)
            ->call('deleteDepartment', $department->id)
            ->assertHasNoErrors();

        $this->assertNotNull($department->fresh());
    }

    public function test_an_unused_department_can_be_deleted(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole('hr-manager');
        $department = Department::factory()->create();

        Livewire::actingAs($hr)->test(DepartmentIndex::class)
            ->call('deleteDepartment', $department->id)
            ->assertHasNoErrors();

        $this->assertNull($department->fresh());
    }
}
