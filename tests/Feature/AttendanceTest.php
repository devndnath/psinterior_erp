<?php

namespace Tests\Feature;

use App\Livewire\Attendance\Form as AttendanceForm;
use App\Livewire\Attendance\Index as AttendanceIndex;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * site-supervisor holds view/create/edit on attendance per
     * RolePermissionSeeder — the exact case ARCHITECTURE.md §3.9's
     * "architected so a biometric API can insert rows later" note is
     * about: site staff, not just HR, can record attendance.
     */
    public function test_a_site_supervisor_can_create_and_edit_attendance(): void
    {
        $supervisor = User::factory()->create();
        $supervisor->assignRole('site-supervisor');

        $employee = Employee::factory()->create();

        Livewire::actingAs($supervisor)->test(AttendanceForm::class)
            ->set('employee_id', (string) $employee->id)
            ->set('date', now()->toDateString())
            ->set('status', 'present')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, Attendance::count());

        $attendance = Attendance::first();

        Livewire::actingAs($supervisor)->test(AttendanceForm::class, ['attendance' => $attendance])
            ->set('status', 'late')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('late', $attendance->fresh()->status->value);
    }

    /**
     * The plain `employee` role only holds view_attendance (no create)
     * per RolePermissionSeeder — must not be able to create or edit.
     */
    public function test_a_plain_employee_role_cannot_create_attendance(): void
    {
        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('employee');

        Livewire::actingAs($employeeUser)->test(AttendanceForm::class)
            ->assertForbidden();
    }

    public function test_a_plain_employee_role_can_view_the_attendance_list(): void
    {
        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('employee');

        Livewire::actingAs($employeeUser)->test(AttendanceIndex::class)
            ->assertSuccessful();
    }

    public function test_the_unique_employee_date_constraint_is_respected_via_update_or_create(): void
    {
        $employee = Employee::factory()->create();
        $service = app(AttendanceService::class);

        $service->create([
            'employee_id' => $employee->id,
            'date' => '2026-10-01',
            'status' => 'present',
        ]);

        $service->create([
            'employee_id' => $employee->id,
            'date' => '2026-10-01',
            'status' => 'late',
        ]);

        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->where('date', '2026-10-01')->count());
        $this->assertSame('late', Attendance::where('employee_id', $employee->id)->where('date', '2026-10-01')->first()->status->value);
    }
}
