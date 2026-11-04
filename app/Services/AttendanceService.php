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
        return $this->upsertForDay($data['employee_id'], $data['date'], [
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'status' => $data['status'],
        ]);
    }

    /**
     * updateOrCreate() keyed on (employee_id, date), but matching the
     * existing row with whereDate(): the `date` cast stores a time part
     * on some drivers (SQLite), so an exact string match on '2026-10-01'
     * would miss the row and hit the unique index instead of updating.
     */
    protected function upsertForDay(int|string $employeeId, string $date, array $values): Attendance
    {
        $existing = Attendance::where('employee_id', $employeeId)->whereDate('date', $date)->first();

        if ($existing) {
            $existing->update($values);

            return $existing;
        }

        return Attendance::create(['employee_id' => $employeeId, 'date' => $date] + $values);
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update([
            'check_in' => array_key_exists('check_in', $data) ? $data['check_in'] : $attendance->check_in,
            'check_out' => array_key_exists('check_out', $data) ? $data['check_out'] : $attendance->check_out,
            'status' => array_key_exists('status', $data) ? $data['status'] : $attendance->status->value,
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
            $this->upsertForDay($employeeId, $date, ['status' => $status->value]);
        }
    }
}
