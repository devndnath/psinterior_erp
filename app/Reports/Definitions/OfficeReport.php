<?php

namespace App\Reports\Definitions;

use App\Models\Expense;
use App\Reports\Filters;
use App\Reports\Report;

/** Every head-office expense (an expense with no project). */
class OfficeReport extends Report
{
    public static function key(): string { return 'office'; }
    public static function title(): string { return 'Office Report'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'Every head-office expense (not tied to a project) — date, head, purpose, paid to and method.'; }

    public function filters(): array { return ['dates', 'category', 'method']; }

    public function columns(): array
    {
        return [
            'date' => ['label' => 'Date', 'type' => 'date'],
            'no' => ['label' => 'Expense #', 'type' => 'text'],
            'head' => ['label' => 'Head', 'type' => 'text'],
            'purpose' => ['label' => 'Purpose', 'type' => 'text'],
            'paid_to' => ['label' => 'Paid to', 'type' => 'text'],
            'method' => ['label' => 'Method', 'type' => 'text'],
            'amount' => ['label' => 'Amount', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $expenses = $f->dates(Expense::query()->whereNull('project_id'), 'date')
            ->when($f->category, fn ($q) => $q->where('category_id', $f->category))
            ->when($f->method, fn ($q) => $q->where('method', $f->method))
            ->with(['category:id,name', 'vendor:id,company_name'])
            ->orderBy('date')->get();

        foreach ($expenses as $e) {
            yield [
                'date' => $e->date,
                'no' => self::cell($e->expense_no, route('expenses.show', $e)),
                'head' => $e->category?->name ?? '—',
                'purpose' => $e->description ?: '—',
                'paid_to' => $e->vendor?->company_name ?? $e->vendor_name ?? '—',
                'method' => $e->method?->label() ?? '—',
                'amount' => (float) $e->amount,
            ];
        }
    }
}
