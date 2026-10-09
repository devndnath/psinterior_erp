<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * spec §49 / §78 rule 6: every important change is recorded, and no
 * normal user — not even one holding other broad permissions — can delete
 * an audit entry.
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_model_with_logs_activity_trait_writes_an_entry(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $department = Department::create(['name' => 'Procurement']);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => $department->getMorphClass(),
            'subject_id' => $department->id,
            'action' => 'created',
            'causer_id' => $user->id,
        ]);
    }

    public function test_updating_a_tracked_attribute_records_old_and_new_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $department = Department::create(['name' => 'Design']);
        $department->update(['name' => 'Design & 3D']);

        $log = ActivityLog::where('subject_id', $department->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Design', $log->old_values['name']);
        $this->assertSame('Design & 3D', $log->new_values['name']);
    }

    public function test_normal_staff_user_cannot_delete_an_activity_log(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['name' => 'HR']);
        $log = ActivityLog::where('subject_id', $department->id)->firstOrFail();

        $this->assertFalse($user->can('delete', $log));
    }

    public function test_super_admin_bypass_still_leaves_no_delete_route_to_call(): void
    {
        // There is deliberately no delete route/controller action for
        // activity_logs anywhere in the application — the policy denying
        // everyone is defence in depth, not the only safeguard.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('activity-logs.destroy'));
    }
}
