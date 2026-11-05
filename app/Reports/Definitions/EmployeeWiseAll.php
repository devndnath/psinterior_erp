<?php

namespace App\Reports\Definitions;

use App\Enums\AttendanceStatus;
use App\Enums\ExpenseClaimStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\Payroll;
use App\Reports\Filters;
use App\Reports\Report;

/** One line per employee: attendance, leave, claims and salary. */
class EmployeeWiseAll extends Report
{
    public static function key(): string { return 'employee-wise-all'; }
    public static function title(): string { return 'Employee Wise All Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_employees'; }
    public static function description(): string { return 'Everything about each employee for a period — attendance, leave, claims and salary.'; }

    public function filters(): array { return ['dates', 'employee']; }

    public function columns(): array
    {
        return [
            'employee' => ['label' => 'Employee', 'type' => 'text'],
            'present' => ['label' => 'Present', 'type' => 'int', 'total' => true],
            'late' => ['label' => 'Late', 'type' => 'int', 'total' => true],
            'absent' => ['label' => 'Absent', 'type' => 'int', 'total' => true],
            'leave' => ['label' => 'Leave', 'type' => 'int', 'total' => true],
            'claims' => ['label' => 'Claims approved', 'type' => 'money', 'total' => true],
            'salary' => ['label' => 'Net salary', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $employees = Employee::query()->when($f->employee, fn ($q) => $q->whereKey($f->employee))
            ->with(['department:id,name', 'designation:id,name'])->orderBy('name')->get();
        $ids = $employees->pluck('id');

        $att = $f->dates(Attendance::whereIn('employee_id', $ids), 'date')->get(['employee_id', 'status'])->groupBy('employee_id');
        $claims = $f->dates(ExpenseClaim::whereIn('employee_id', $ids)->where('status', ExpenseClaimStatus::Approved->value), 'date')
            ->selectRaw('employee_id, SUM(amount) as v')->groupBy('employee_id')->pluck('v', 'employee_id');
        [$fromMonth, $toMonth] = $f->monthBounds();
        $salary = Payroll::whereIn('employee_id', $ids)
            ->when($fromMonth, fn ($q) => $q->where('month', '>=', $fromMonth))
            ->when($toMonth, fn ($q) => $q->where('month', '<=', $toMonth))
            ->selectRaw('employee_id, SUM(net_salary) as v')->groupBy('employee_id')->pluck('v', 'employee_id');

        foreach ($employees as $e) {
            $a = $att[$e->id] ?? collect();
            yield [
                'employee' => self::cell($e->name, route('employees.show', $e), trim(($e->designation?->name ?? '').' · '.($e->department?->name ?? ''), ' ·')),
                'present' => $a->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Overtime])->count(),
                'late' => $a->where('status', AttendanceStatus::Late)->count(),
                'absent' => $a->where('status', AttendanceStatus::Absent)->count(),
                'leave' => $a->where('status', AttendanceStatus::Leave)->count(),
                'claims' => (float) ($claims[$e->id] ?? 0),
                'salary' => (float) ($salary[$e->id] ?? 0),
            ];
        }
    }
}
