<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;

/**
 * Plain CRUD plus a convenience bulk-marker. No side effects outside the
 * `attendance` table itself — unlike Payroll/Leave, a single attendance
 * row never cascades into another aggregate.
 */
class AttendanceService
{
    public function create(array $data): Attendance
    {
        return Attendance::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            [
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'status' => $data['status'],
            ]
        );
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update([
            'check_in' => $data['check_in'] ?? $attendance->check_in,
            'check_out' => $data['check_out'] ?? $attendance->check_out,
            'status' => $data['status'] ?? $attendance->status->value,
        ]);

        return $attendance->fresh();
    }

    /**
     * Marks a whole team present/absent/etc. for one date in a single
     * call — a loop calling updateOrCreate() per employee, respecting
     * the (employee_id, date) uniqueness constraint rather than fighting
     * it with a bulk insert.
     *
     * @param  array<int, int>  $employeeIds
     */
    public function markBulkForDate(array $employeeIds, string $date, AttendanceStatus $status): void
    {
        foreach ($employeeIds as $employeeId) {
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                ['status' => $status->value]
            );
        }
    }
}
