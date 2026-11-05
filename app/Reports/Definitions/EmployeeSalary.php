<?php

namespace App\Reports\Definitions;

use App\Models\Payroll;
use App\Reports\Filters;
use App\Reports\Report;

/** Payroll lines per employee and month. */
class EmployeeSalary extends Report
{
    public static function key(): string { return 'employee-salary'; }
    public static function title(): string { return 'Employee Salary Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_payroll'; }
    public static function description(): string { return 'Salary per employee per month — basic, allowances, conveyance, overtime, deductions and net pay.'; }

    public function filters(): array { return ['dates', 'employee']; }
    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'Payroll months that fall in the date range. Deductions include advances.';
    }

    public function columns(): array
    {
        return [
            'employee' => ['label' => 'Employee', 'type' => 'text'],
            'month' => ['label' => 'Month', 'type' => 'text'],
            'basic' => ['label' => 'Basic', 'type' => 'money', 'total' => true],
            'allowance' => ['label' => 'Allowance', 'type' => 'money', 'total' => true],
            'conveyance' => ['label' => 'Conveyance', 'type' => 'money', 'total' => true],
            'overtime' => ['label' => 'Overtime + bonus', 'type' => 'money', 'total' => true],
            'deduction' => ['label' => 'Deductions', 'type' => 'money', 'total' => true],
            'net' => ['label' => 'Net salary', 'type' => 'money', 'total' => true],
            'status' => ['label' => 'Status', 'type' => 'text'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        [$fromMonth, $toMonth] = $f->monthBounds();
        $payrolls = Payroll::query()
            ->when($fromMonth, fn ($q) => $q->where('month', '>=', $fromMonth))
            ->when($toMonth, fn ($q) => $q->where('month', '<=', $toMonth))
            ->when($f->employee, fn ($q) => $q->where('employee_id', $f->employee))
            ->with('employee:id,name,employee_id')->get()
            ->sortBy(fn ($p) => ($p->employee?->name ?? '').'|'.$p->month);

        foreach ($payrolls as $p) {
            yield [
                'employee' => self::cell($p->employee?->name ?? '—', $p->employee ? route('employees.show', $p->employee) : null, $p->employee?->employee_id),
                'month' => \Carbon\CarbonImmutable::createFromFormat('Y-m-d', $p->month.'-01')->format('M Y'),
                'basic' => (float) $p->basic,
                'allowance' => (float) $p->allowance,
                'conveyance' => (float) $p->conveyance,
                'overtime' => (float) $p->overtime + (float) $p->bonus,
                'deduction' => (float) $p->deduction + (float) $p->advance,
                'net' => (float) $p->net_salary,
                'status' => $p->status->label(),
            ];
        }
    }
}
