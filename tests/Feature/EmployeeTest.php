<?php

namespace Tests\Feature;

use App\Livewire\Employees\Form as EmployeeForm;
use App\Livewire\Employees\Index as EmployeeIndex;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_employees', 'create_employees', 'edit_employees', 'delete_employees']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_employee_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(EmployeeIndex::class)
            ->assertForbidden();
    }

    public function test_creating_an_employee_assigns_a_sequential_employee_number(): void
    {
        Livewire::actingAs($this->staff)->test(EmployeeForm::class)
            ->set('name', 'Jane Doe')
            ->set('joining_date', now()->toDateString())
            ->set('salary', '50000')
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $employee = Employee::first();
        $this->assertNotNull($employee);
        $this->assertStringStartsWith('EMP-', $employee->employee_id);
    }

    public function test_creating_a_second_employee_increments_the_sequence(): void
    {
        Livewire::actingAs($this->staff)->test(EmployeeForm::class)
            ->set('name', 'First Employee')
            ->set('joining_date', now()->toDateString())
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->call('save');

        Livewire::actingAs($this->staff)->test(EmployeeForm::class)
            ->set('name', 'Second Employee')
            ->set('joining_date', now()->toDateString())
            ->set('employment_type', 'full_time')
            ->set('status', 'active')
            ->call('save');

        $this->assertSame(2, Employee::count());
        $codes = Employee::orderBy('id')->pluck('employee_id')->all();
        $this->assertNotSame($codes[0], $codes[1]);
    }
}
