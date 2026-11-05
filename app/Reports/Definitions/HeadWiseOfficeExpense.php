<?php

namespace App\Reports\Definitions;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Reports\Filters;
use App\Reports\Report;

/** Head-office expenses totalled by expense head (category). */
class HeadWiseOfficeExpense extends Report
{
    public static function key(): string { return 'head-wise-office-expense'; }
    public static function title(): string { return 'Head Wise Office Expense'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'Head-office spending totalled by expense head, with each head\'s share.'; }

    public function columns(): array
    {
        return [
            'head' => ['label' => 'Expense head', 'type' => 'text'],
            'entries' => ['label' => 'Entries', 'type' => 'int', 'total' => true],
            'amount' => ['label' => 'Amount', 'type' => 'money', 'total' => true],
            'share' => ['label' => 'Share', 'type' => 'pct'],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $by = $f->dates(Expense::query()->whereNull('project_id'), 'date')
            ->selectRaw('category_id, COUNT(*) as n, SUM(amount) as total')->groupBy('category_id')->get();
        $names = ExpenseCategory::whereIn('id', $by->pluck('category_id'))->pluck('name', 'id');
        $sum = (float) $by->sum('total');

        foreach ($by->sortByDesc('total') as $row) {
            yield [
                'head' => $names[$row->category_id] ?? 'Uncategorised',
                'entries' => (int) $row->n,
                'amount' => (float) $row->total,
                'share' => $sum > 0 ? round($row->total / $sum * 100, 1) : 0,
            ];
        }
    }
}
