<?php

namespace App\Reports\Definitions;

use App\Enums\ExpenseClaimStatus;
use App\Models\ExpenseClaim;
use App\Reports\Filters;
use App\Reports\Report;

/** Conveyance and other claims per employee, by status. */
class EmployeeConveyance extends Report
{
    public static function key(): string { return 'employee-conveyance'; }
    public static function title(): string { return 'Employee Wise Conveyance'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'Each employee\'s conveyance claims — claimed, approved, paid through payroll and still unpaid.'; }

    public function filters(): array { return ['dates', 'employee']; }

    public function note(): ?string
    {
        return 'Claims dated in the range. Paid = approved claims already included in a payroll; unpaid = approved but not yet in a payroll.';
    }

    public function columns(): array
    {
        return [
            'employee' => ['label' => 'Employee', 'type' => 'text'],
            'claims' => ['label' => 'Claims', 'type' => 'int', 'total' => true],
            'claimed' => ['label' => 'Claimed', 'type' => 'money', 'total' => true],
            'pending' => ['label' => 'Pending', 'type' => 'money', 'total' => true],
            'rejected' => ['label' => 'Rejected', 'type' => 'money', 'total' => true],
            'paid' => ['label' => 'Paid (payroll)', 'type' => 'money', 'total' => true],
            'unpaid' => ['label' => 'Approved, unpaid', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $claims = $f->dates(ExpenseClaim::query(), 'date')
            ->when($f->employee, fn ($q) => $q->where('employee_id', $f->employee))
            ->with('employee:id,name,employee_id')->get()->groupBy('employee_id')
            ->sortBy(fn ($g) => $g->first()->employee?->name);

        foreach ($claims as $g) {
            $e = $g->first()->employee;
            $approved = $g->where('status', ExpenseClaimStatus::Approved);
            yield [
                'employee' => self::cell($e?->name ?? '—', $e ? route('employees.show', $e) : null, $e?->employee_id),
                'claims' => $g->count(),
                'claimed' => (float) $g->sum('amount'),
                'pending' => (float) $g->where('status', ExpenseClaimStatus::Pending)->sum('amount'),
                'rejected' => (float) $g->where('status', ExpenseClaimStatus::Rejected)->sum('amount'),
                'paid' => (float) $approved->whereNotNull('payroll_id')->sum('amount'),
                'unpaid' => (float) $approved->whereNull('payroll_id')->sum('amount'),
            ];
        }
    }
}
