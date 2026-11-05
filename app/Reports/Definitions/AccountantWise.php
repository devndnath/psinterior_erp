<?php

namespace App\Reports\Definitions;

use App\Models\Expense;
use App\Models\User;
use App\Reports\Filters;
use App\Reports\Report;

/** Expenses grouped by the staff member who recorded them. */
class AccountantWise extends Report
{
    public static function key(): string { return 'accountant-wise'; }
    public static function title(): string { return 'Accountant Wise All Expense'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'Expenses grouped by who recorded them — project and office, count and total.'; }

    public function filters(): array { return ['dates', 'user']; }

    public function columns(): array
    {
        return [
            'user' => ['label' => 'Recorded by', 'type' => 'text'],
            'entries' => ['label' => 'Entries', 'type' => 'int', 'total' => true],
            'project' => ['label' => 'Project expenses', 'type' => 'money', 'total' => true],
            'office' => ['label' => 'Office expenses', 'type' => 'money', 'total' => true],
            'total' => ['label' => 'Total', 'type' => 'money', 'total' => true],
            'last' => ['label' => 'Last entry', 'type' => 'date'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $by = $f->dates(Expense::query(), 'date')
            ->when($f->user, fn ($q) => $q->where('created_by', $f->user))
            ->selectRaw('created_by, COUNT(*) as n, SUM(CASE WHEN project_id IS NULL THEN 0 ELSE amount END) as proj, SUM(CASE WHEN project_id IS NULL THEN amount ELSE 0 END) as office, SUM(amount) as total, MAX(date) as last')
            ->groupBy('created_by')->get();
        $names = User::whereIn('id', $by->pluck('created_by')->filter())->pluck('name', 'id');

        foreach ($by->sortByDesc('total') as $row) {
            yield [
                'user' => $row->created_by ? ($names[$row->created_by] ?? 'Former user') : 'Not recorded',
                'entries' => (int) $row->n,
                'project' => (float) $row->proj,
                'office' => (float) $row->office,
                'total' => (float) $row->total,
                'last' => $row->last,
            ];
        }
    }
}
