<?php

namespace App\Reports\Definitions;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Reports\Filters;
use App\Reports\Report;

/** All expenses (project + office) by category. */
class CategoryWise extends Report
{
    public static function key(): string { return 'category-wise'; }
    public static function title(): string { return 'Category Wise Report'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'All spending by expense category — project and office side by side.'; }

    public function filters(): array { return ['dates', 'project']; }

    public function columns(): array
    {
        return [
            'category' => ['label' => 'Category', 'type' => 'text'],
            'entries' => ['label' => 'Entries', 'type' => 'int', 'total' => true],
            'project' => ['label' => 'Project expenses', 'type' => 'money', 'total' => true],
            'office' => ['label' => 'Office expenses', 'type' => 'money', 'total' => true],
            'total' => ['label' => 'Total', 'type' => 'money', 'total' => true],
            'share' => ['label' => 'Share', 'type' => 'pct'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $by = $f->dates(Expense::query(), 'date')
            ->when($f->project, fn ($q) => $q->where('project_id', $f->project))
            ->selectRaw('category_id, COUNT(*) as n, SUM(CASE WHEN project_id IS NULL THEN 0 ELSE amount END) as proj, SUM(CASE WHEN project_id IS NULL THEN amount ELSE 0 END) as office, SUM(amount) as total')
            ->groupBy('category_id')->get();
        $names = ExpenseCategory::whereIn('id', $by->pluck('category_id'))->pluck('name', 'id');
        $sum = (float) $by->sum('total');

        foreach ($by->sortByDesc('total') as $row) {
            yield [
                'category' => $names[$row->category_id] ?? 'Uncategorised',
                'entries' => (int) $row->n,
                'project' => (float) $row->proj,
                'office' => (float) $row->office,
                'total' => (float) $row->total,
                'share' => $sum > 0 ? round($row->total / $sum * 100, 1) : 0,
            ];
        }
    }
}
