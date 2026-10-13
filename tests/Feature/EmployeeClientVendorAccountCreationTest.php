<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Employees\Form as EmployeeForm;
use App\Livewire\Vendors\Form as VendorForm;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the "create a login for this person" inline checkbox added to
 * Employees/Clients/Vendors\Form (the audit gap closed this phase: those
 * three models' `user_id` FK has existed since Phase 1 with no UI that
 * ever set it). Employee is the higher-risk case — it can grant real
 * Spatie roles — so it gets the extra `create_users` gate; Client/Vendor
 * portal logins never hold roles (ownership-based access, see
 * ARCHITECTURE.md §5/§7) so they need no extra gate beyond the
 * create/update permission the form already requires.
 */
class EmployeeClientVendorAccountCreationTest extends TestCase
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

    protected function hrManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('hr-manager');

        return $user;
    }

    protected function procurementOfficer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('procurement-officer');

        return $user;
    }

    // --- Employee ------------------------------------------------------

    public function test_an_admin_can_create_an_employee_with_a_staff_login_and_roles(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(EmployeeForm::class)
            ->set('name', 'New Hire')
            ->set('email', 'new-hire@example.test')
            ->set('joining_date', now()->toDateString())
            ->set('salary', '50000')
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->set('create_account', true)
            ->set('account_roles', ['site-engineer'])
            ->call('save')
            ->assertHasNoErrors();

        $employee = Employee::where('name', 'New Hire')->first();
        $this->assertNotNull($employee);
        $this->assertNotNull($employee->user_id);

        $user = $employee->user;
        $this->assertSame('new-hire@example.test', $user->email);
        $this->assertTrue($user->user_type === UserType::Staff);
        $this->assertTrue($user->hasRole('site-engineer'));
        $this->assertTrue($user->is_active);

        $this->assertNotNull(session('generated_password'));
        $this->assertSame($user->email, session('generated_password_for'));
    }

    public function test_the_super_admin_role_cannot_be_granted_through_the_employee_checkbox(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(EmployeeForm::class)
            ->set('name', 'Sneaky Hire')
            ->set('email', 'sneaky@example.test')
            ->set('joining_date', now()->toDateString())
            ->set('salary', '50000')
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->set('create_account', true)
            ->set('account_roles', ['super-admin'])
            ->call('save')
            ->assertHasNoErrors();

        $employee = Employee::where('name', 'Sneaky Hire')->first();
        $this->assertNotNull($employee->user_id);
        $this->assertFalse($employee->user->hasRole('super-admin'));
    }

    public function test_an_hr_manager_without_create_users_cannot_create_a_login_via_the_employee_checkbox(): void
    {
        $hr = $this->hrManager();

        Livewire::actingAs($hr)->test(EmployeeForm::class)
            ->set('name', 'Blocked Hire')
            ->set('email', 'blocked@example.test')
            ->set('joining_date', now()->toDateString())
            ->set('salary', '40000')
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->set('create_account', true)
            ->call('save')
            ->assertForbidden();

        $this->assertNull(Employee::where('name', 'Blocked Hire')->first());
        $this->assertNull(User::where('email', 'blocked@example.test')->first());
    }

    public function test_an_hr_manager_can_still_create_an_employee_without_a_login(): void
    {
        $hr = $this->hrManager();

        Livewire::actingAs($hr)->test(EmployeeForm::class)
            ->set('name', 'Plain Hire')
            ->set('joining_date', now()->toDateString())
            ->set('salary', '40000')
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $employee = Employee::where('name', 'Plain Hire')->first();
        $this->assertNotNull($employee);
        $this->assertNull($employee->user_id);
    }

    public function test_the_account_checkbox_is_not_offered_again_once_an_employee_already_has_a_login(): void
    {
        $admin = $this->admin();
        $employee = Employee::factory()->create();
        $existingUser = User::factory()->create(['user_type' => UserType::Staff]);
        $employee->update(['user_id' => $existingUser->id]);

        Livewire::actingAs($admin)->test(EmployeeForm::class, ['employee' => $employee])
            ->set('create_account', true)
            ->set('account_roles', ['site-engineer'])
            ->call('save')
            ->assertHasNoErrors();

        $employee->refresh();
        $this->assertSame($existingUser->id, $employee->user_id);
        $this->assertFalse($existingUser->fresh()->hasRole('site-engineer'));
    }

    // --- Client ----------------------------------------------------------

    public function test_an_admin_can_create_a_client_with_portal_access_and_no_roles(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'New Client')
            ->set('type', 'individual')
            ->set('email', 'client@example.test')
            ->set('create_account', true)
            ->call('save')
            ->assertHasNoErrors();

        $client = Client::where('name', 'New Client')->first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->user_id);

        $portalUser = $client->portalUser;
        $this->assertSame('client@example.test', $portalUser->email);
        $this->assertTrue($portalUser->user_type === UserType::Client);
        $this->assertCount(0, $portalUser->getRoleNames());

        $this->assertNotNull(session('generated_password'));
        $this->assertSame($portalUser->email, session('generated_password_for'));
    }

    public function test_a_client_can_still_be_created_without_portal_access(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(ClientForm::class)
            ->set('name', 'No Portal Client')
            ->set('type', 'individual')
            ->call('save')
            ->assertHasNoErrors();

        $client = Client::where('name', 'No Portal Client')->first();
        $this->assertNotNull($client);
        $this->assertNull($client->user_id);
    }

    // --- Vendor ------------------------------------------------------------

    public function test_a_procurement_officer_can_create_a_vendor_with_portal_access(): void
    {
        $officer = $this->procurementOfficer();

        Livewire::actingAs($officer)->test(VendorForm::class)
            ->set('company_name', 'Acme Supplies')
            ->set('email', 'vendor@example.test')
            ->set('create_account', true)
            ->call('save')
            ->assertHasNoErrors();

        $vendor = Vendor::where('company_name', 'Acme Supplies')->first();
        $this->assertNotNull($vendor);
        $this->assertNotNull($vendor->user_id);

        $portalUser = $vendor->user;
        $this->assertSame('vendor@example.test', $portalUser->email);
        $this->assertTrue($portalUser->user_type === UserType::Vendor);
        $this->assertCount(0, $portalUser->getRoleNames());
    }

    public function test_vendor_portal_account_uses_company_name_when_no_contact_person_is_given(): void
    {
        $officer = $this->procurementOfficer();

        Livewire::actingAs($officer)->test(VendorForm::class)
            ->set('company_name', 'Nameless Supplies')
            ->set('contact_person', '')
            ->set('email', 'nameless@example.test')
            ->set('create_account', true)
            ->call('save')
            ->assertHasNoErrors();

        $vendor = Vendor::where('company_name', 'Nameless Supplies')->first();
        $this->assertSame('Nameless Supplies', $vendor->user->name);
    }
}
