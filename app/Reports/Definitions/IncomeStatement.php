<?php

namespace App\Reports\Definitions;

use App\Enums\InvoiceType;
use App\Enums\PayrollStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SubcontractorPayment;
use App\Reports\Filters;
use App\Reports\Report;
use Illuminate\Support\Collection;

/** Income vs expenditure for a period, cash basis. */
class IncomeStatement extends Report
{
    public static function key(): string { return 'income-statement'; }
    public static function title(): string { return 'Income Statement'; }
    public static function group(): string { return 'accounts'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'Money received against money spent for a period — income by source, spending by head — and the net result.'; }

    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'Cash basis: income is payments received; expenditure is expenses recorded, contractor payments and salaries marked paid (by payroll month). Staff claims are paid through payroll, so they sit inside salaries.';
    }

    public function columns(): array
    {
        return [
            'section' => ['label' => 'Section', 'type' => 'text'],
            'particular' => ['label' => 'Particular', 'type' => 'text'],
            'income' => ['label' => 'Income', 'type' => 'money', 'total' => true],
            'expense' => ['label' => 'Expenditure', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $payments = $f->dates(Payment::query(), 'date');
        $design = (float) (clone $payments)->where(fn ($q) => $q->whereNull('project_id')
            ->orWhereHas('invoice', fn ($i) => $i->where('type', InvoiceType::Design->value)))->sum('amount');
        $all = (float) (clone $payments)->sum('amount');

        yield ['section' => 'Income', 'particular' => 'Project receipts', 'income' => $all - $design, 'expense' => null];
        yield ['section' => 'Income', 'particular' => 'Design sales', 'income' => $design, 'expense' => null];

        $byCategory = $f->dates(Expense::query(), 'date')
            ->selectRaw('category_id, SUM(amount) as total')->groupBy('category_id')->pluck('total', 'category_id');
        $names = ExpenseCategory::whereIn('id', $byCategory->keys())->pluck('name', 'id');
        foreach ($byCategory->sortDesc() as $id => $total) {
            yield ['section' => 'Expenditure', 'particular' => $names[$id] ?? 'Uncategorised', 'income' => null, 'expense' => (float) $total];
        }

        yield ['section' => 'Expenditure', 'particular' => 'Contractor payments', 'income' => null,
            'expense' => (float) $f->dates(SubcontractorPayment::query(), 'date')->sum('amount')];

        [$fromMonth, $toMonth] = $f->monthBounds();
        yield ['section' => 'Expenditure', 'particular' => 'Salaries paid', 'income' => null,
            'expense' => (float) Payroll::where('status', PayrollStatus::Paid->value)
                ->when($fromMonth, fn ($q) => $q->where('month', '>=', $fromMonth))
                ->when($toMonth, fn ($q) => $q->where('month', '<=', $toMonth))
                ->sum('net_salary')];
    }

    public function summary(Collection $rows, array $totals): array
    {
        $net = ($totals['income'] ?? 0) - ($totals['expense'] ?? 0);

        return [
            ['label' => 'Total income', 'value' => $totals['income'] ?? 0, 'type' => 'money', 'tone' => 'green'],
            ['label' => 'Total expenditure', 'value' => $totals['expense'] ?? 0, 'type' => 'money', 'tone' => 'gold'],
            ['label' => $net >= 0 ? 'Net profit' : 'Net loss', 'value' => $net, 'type' => 'money', 'tone' => $net >= 0 ? 'dark' : 'red'],
        ];
    }
}
