<?php

namespace Tests\Feature;

use App\Enums\LeaveApprovalStatus;
use App\Livewire\Leaves\Form as LeaveForm;
use App\Livewire\Leaves\Show as LeaveShow;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_creating_a_leave_starts_both_approval_statuses_pending(): void
    {
        $hr = User::factory()->create();
        $hr->givePermissionTo(['create_leaves']);

        $employee = Employee::factory()->create();

        Livewire::actingAs($hr)->test(LeaveForm::class)
            ->set('employee_id', (string) $employee->id)
            ->set('type', 'casual')
            ->set('from_date', now()->toDateString())
            ->set('to_date', now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $leave = Leave::first();
        $this->assertNotNull($leave);
        $this->assertSame(LeaveApprovalStatus::Pending, $leave->manager_status);
        $this->assertSame(LeaveApprovalStatus::Pending, $leave->hr_status);
    }

    /**
     * The dedicated test proving the permission-gating decision
     * documented on LeavePolicy: a user holding ONLY `edit_leaves` (no
     * invented "manager approval" ability exists to hold instead) can
     * perform BOTH approveByManager() and approveByHr() — mirroring
     * Phase 8's PurchaseRequestTest pattern for the same kind of
     * decision.
     */
    public function test_a_user_with_only_edit_leaves_can_perform_both_approval_steps(): void
    {
        $hrManagerStyleUser = User::factory()->create();
        $hrManagerStyleUser->givePermissionTo(['view_leaves', 'edit_leaves']);

        $leave = Leave::factory()->create();

        Livewire::actingAs($hrManagerStyleUser)->test(LeaveShow::class, ['leave' => $leave])
            ->call('approveByManager')
            ->assertHasNoErrors()
            ->call('approveByHr')
            ->assertHasNoErrors();

        $leave->refresh();
        $this->assertSame(LeaveApprovalStatus::Approved, $leave->manager_status);
        $this->assertSame(LeaveApprovalStatus::Approved, $leave->hr_status);
        $this->assertTrue($leave->isFullyApproved());
    }

    public function test_a_user_without_edit_leaves_cannot_approve(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_leaves']);

        $leave = Leave::factory()->create();

        Livewire::actingAs($user)->test(LeaveShow::class, ['leave' => $leave])
            ->call('approveByManager')
            ->assertForbidden();
    }

    public function test_is_fully_approved_and_is_rejected_helpers(): void
    {
        $leave = Leave::factory()->create([
            'manager_status' => LeaveApprovalStatus::Approved->value,
            'hr_status' => LeaveApprovalStatus::Pending->value,
        ]);

        $this->assertFalse($leave->isFullyApproved());
        $this->assertFalse($leave->isRejected());

        $leave->update(['hr_status' => LeaveApprovalStatus::Approved->value]);
        $this->assertTrue($leave->isFullyApproved());

        $leave->update(['manager_status' => LeaveApprovalStatus::Rejected->value]);
        $this->assertTrue($leave->isRejected());
        $this->assertFalse($leave->isFullyApproved());
    }

    public function test_reject_rejects_the_given_stage_only(): void
    {
        $leave = Leave::factory()->create();

        app(\App\Services\LeaveService::class)->reject($leave, 'manager');

        $leave->refresh();
        $this->assertSame(LeaveApprovalStatus::Rejected, $leave->manager_status);
        $this->assertSame(LeaveApprovalStatus::Pending, $leave->hr_status);
    }

    public function test_reject_throws_for_an_unknown_stage(): void
    {
        $leave = Leave::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        app(\App\Services\LeaveService::class)->reject($leave, 'director');
    }
}
