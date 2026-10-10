<?php

namespace App\Services;

use App\Enums\LeaveApprovalStatus;
use App\Models\Employee;
use App\Models\Leave;

/**
 * Owns the two-stage leave approval (ARCHITECTURE.md §3.9:
 * `manager_status` + `hr_status`, both backed by
 * App\Enums\LeaveApprovalStatus).
 *
 * Deliberate simplification: this service does NOT enforce that
 * `approveByManager()` runs before `approveByHr()`, or vice versa. The
 * data model has two independent columns with no ordering constraint in
 * ARCHITECTURE.md's bare column list, and — per the permission-gating
 * decision documented on LeavePolicy — today only the `hr-manager` role
 * can call either method at all (no distinct "manager approval"
 * ability is seeded), so enforcing an order between two steps performed
 * by the same role would add friction without adding any real control.
 * A future phase that gives a separate role a manager-only ability can
 * revisit this.
 */
class LeaveService
{
    public function create(Employee $employee, array $data): Leave
    {
        return $employee->leaves()->create([
            'type' => $data['type'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'reason' => $data['reason'] ?? null,
            'manager_status' => LeaveApprovalStatus::Pending->value,
            'hr_status' => LeaveApprovalStatus::Pending->value,
        ]);
    }

    public function approveByManager(Leave $leave): Leave
    {
        $leave->update(['manager_status' => LeaveApprovalStatus::Approved->value]);

        return $leave->fresh();
    }

    public function approveByHr(Leave $leave): Leave
    {
        $leave->update(['hr_status' => LeaveApprovalStatus::Approved->value]);

        return $leave->fresh();
    }

    public function reject(Leave $leave, string $stage): Leave
    {
        $column = match ($stage) {
            'manager' => 'manager_status',
            'hr' => 'hr_status',
            default => throw new \InvalidArgumentException("Unknown leave approval stage: {$stage}"),
        };

        $leave->update([$column => LeaveApprovalStatus::Rejected->value]);

        return $leave->fresh();
    }
}
