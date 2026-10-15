<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\ExpenseClaimStatus;
use App\Enums\PayrollStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\Payroll;
use Illuminate\Support\Carbon;

/**
 * Owns the Payroll aggregate. `generate()` is safe to call repeatedly
 * for the same employee+month while the payroll is still Draft (it's an
 * updateOrCreate), but refuses outright once it has left Draft —
 * mirroring InvoiceService::update()'s "a paid invoice is never edited"
 * refusal — so a Finalized/Paid payroll's figures can never be silently
 * recomputed out from under an already-issued payslip.
 */
class PayrollService
{
    /** Standard working hours/day used to derive a per-day and an hourly rate. */
    protected const STANDARD_HOURS_PER_DAY = 8;

    /** Overtime is paid at 1.5x the derived hourly rate — the common default absent a configured policy. */
    protected const OVERTIME_MULTIPLIER = 1.5;

    public function generate(Employee $employee, string $month, array $data): Payroll
    {
        $existing = Payroll::where('employee_id', $employee->id)->where('month', $month)->first();

        if ($existing && $existing->status !== PayrollStatus::Draft) {
            throw new \RuntimeException("Payroll for {$employee->name} / {$month} is already {$existing->status->label()} and cannot be regenerated.");
        }

        $basic = (float) ($data['basic'] ?? 0);
        $allowance = (float) ($data['allowance'] ?? 0);
        $conveyance = (float) ($data['conveyance'] ?? 0);
        $overtime = (float) ($data['overtime'] ?? 0);
        $bonus = (float) ($data['bonus'] ?? 0);
        $deduction = (float) ($data['deduction'] ?? 0);
        $advance = (float) ($data['advance'] ?? 0);

        $netSalary = $basic + $allowance + $conveyance + $overtime + $bonus - $deduction - $advance;

        $payroll = Payroll::updateOrCreate(
            ['employee_id' => $employee->id, 'month' => $month],
            [
                'basic' => $basic,
                'allowance' => $allowance,
                'conveyance' => $conveyance,
                'overtime' => $overtime,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'advance' => $advance,
                'net_salary' => $netSalary,
                'status' => PayrollStatus::Draft->value,
            ]
        );

        // Mark every claim this run's conveyance figure was computed from
        // as spoken for, so computeDraft() never offers it again on a
        // future run for this or any other month (see the
        // expense_claims migration's docblock on `payroll_id`).
        if (! empty($data['claim_ids'])) {
            ExpenseClaim::where('employee_id', $employee->id)
                ->whereIn('id', $data['claim_ids'])
                ->whereNull('payroll_id')
                ->update(['payroll_id' => $payroll->id]);
        }

        return $payroll;
    }

    /**
     * Computes a suggested Draft payroll for $employee/$month from real
     * data already in the system — attendance (present/absent/overtime
     * hours), the employee's own approved-but-unconsumed conveyance
     * claims, and the employee's configured basic salary — so HR starts
     * from a filled-in form instead of typing every figure by hand.
     * Bonus/advance are left at 0 (no data source for those exists);
     * everything returned here is still a plain editable suggestion,
     * never saved until generate() is called.
     *
     * The per-day/per-hour rate is derived from the employee's basic
     * salary divided across the calendar days in the month (this app has
     * no separate "working days" or holiday calendar to divide by
     * instead) at a standard 8-hour day; overtime is paid at 1.5x that
     * hourly rate.
     */
    public function computeDraft(Employee $employee, string $month): array
    {
        // Built from an explicit day-1, never Carbon::createFromFormat('Y-m',
        // ...) — that leaves the day-of-month defaulted from "now", which
        // overflows into the following month whenever today's day-of-month
        // doesn't exist in the target month (e.g. generating for February
        // while today is the 31st), silently computing the wrong month.
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();
        $daysInMonth = $start->daysInMonth;

        $basic = (float) $employee->salary;
        $perDayRate = $daysInMonth > 0 ? $basic / $daysInMonth : 0.0;
        $hourlyRate = $perDayRate / self::STANDARD_HOURS_PER_DAY;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays = $attendance->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late, AttendanceStatus::Overtime])->count();
        $absentDays = $attendance->where('status', AttendanceStatus::Absent)->count();
        $leaveDays = $attendance->where('status', AttendanceStatus::Leave)->count();

        $overtimeHours = 0.0;

        foreach ($attendance as $record) {
            if (! $record->check_in || ! $record->check_out) {
                continue;
            }

            $hoursWorked = $record->check_in->diffInMinutes($record->check_out) / 60;
            $overtimeHours += max(0.0, $hoursWorked - self::STANDARD_HOURS_PER_DAY);
        }

        $overtimeHours = round($overtimeHours, 2);
        $overtimePay = round($overtimeHours * $hourlyRate * self::OVERTIME_MULTIPLIER, 2);

        // Unpaid-absence deduction only — a day on approved Leave is not
        // deducted (that's the point of paid leave); Late/Overtime/Present
        // are all treated as a full paid day.
        $deduction = round($absentDays * $perDayRate, 2);

        $claims = ExpenseClaim::where('employee_id', $employee->id)
            ->where('status', ExpenseClaimStatus::Approved->value)
            ->whereNull('payroll_id')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $conveyance = (float) $claims->sum('amount');

        return [
            'basic' => round($basic, 2),
            'overtime' => $overtimePay,
            'conveyance' => round($conveyance, 2),
            'deduction' => $deduction,
            'claim_ids' => $claims->pluck('id')->all(),
            'summary' => [
                'days_in_month' => $daysInMonth,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'overtime_hours' => $overtimeHours,
                'claims' => $claims,
            ],
        ];
    }

    public function finalize(Payroll $payroll): Payroll
    {
        if ($payroll->status !== PayrollStatus::Draft) {
            throw new \RuntimeException('Only a draft payroll can be finalized.');
        }

        $payroll->update(['status' => PayrollStatus::Finalized->value]);

        return $payroll->fresh();
    }

    public function markPaid(Payroll $payroll): Payroll
    {
        if ($payroll->status !== PayrollStatus::Finalized) {
            throw new \RuntimeException('Only a finalized payroll can be marked paid.');
        }

        $payroll->update(['status' => PayrollStatus::Paid->value]);

        return $payroll->fresh();
    }
}
