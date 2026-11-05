<?php

namespace App\Reports\Definitions;

use App\Enums\PayrollStatus;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SubcontractorPayment;
use App\Reports\Filters;
use App\Reports\Report;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Every receipt and payment in date order with a running balance,
 * starting from the balance carried in before the period. Filter by
 * method for a cash-only (or bank-only) book.
 */
class CashBook extends Report
{
    private float $opening = 0;

    public static function key(): string { return 'cash-book'; }
    public static function title(): string { return 'Cash Book'; }
    public static function group(): string { return 'accounts'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'Every receipt and payment in date order with a running balance — filter by method for a cash or bank book.'; }

    public function filters(): array { return ['dates', 'method']; }

    public function note(): ?string
    {
        return 'Receipts are client payments; payments are expenses, contractor payments and paid salaries. Contractor payments and salaries carry no method, so they are left out when a method is chosen. Salaries are dated the day the payroll was marked paid.';
    }

    public function columns(): array
    {
        return [
            'date' => ['label' => 'Date', 'type' => 'date'],
            'voucher' => ['label' => 'Voucher', 'type' => 'text'],
            'particular' => ['label' => 'Particular', 'type' => 'text'],
            'method' => ['label' => 'Method', 'type' => 'text'],
            'in' => ['label' => 'Received', 'type' => 'money', 'total' => true],
            'out' => ['label' => 'Paid', 'type' => 'money', 'total' => true],
            'balance' => ['label' => 'Balance', 'type' => 'money'],
        ];
    }

    /** All entries, unsorted, optionally only those before $before. */
    private function entries(Filters $f, bool $beforeRange): Collection
    {
        $range = fn ($q, $col) => $beforeRange
            ? ($f->from ? $q->whereDate($col, '<', $f->from->toDateString()) : $q->whereRaw('1 = 0'))
            : $f->dates($q, $col);
        $method = fn ($q) => $q->when($f->method, fn ($q) => $q->where('method', $f->method));

        $rows = collect();

        foreach ($method($range(Payment::query()->with('client:id,name'), 'date'))->get() as $p) {
            $rows->push(['date' => $p->date, 'voucher' => self::cell($p->payment_no, route('payments.show', $p)),
                'particular' => 'Received from '.($p->client?->name ?? 'client'), 'method' => $p->method?->label(), 'in' => (float) $p->amount, 'out' => null]);
        }
        foreach ($method($range(Expense::query()->with(['category:id,name', 'project:id,project_code']), 'date'))->get() as $e) {
            $rows->push(['date' => $e->date, 'voucher' => self::cell($e->expense_no, route('expenses.show', $e)),
                'particular' => ($e->description ?: $e->category?->name ?? 'Expense').' — '.($e->project?->project_code ?? 'Office'),
                'method' => $e->method?->label(), 'in' => null, 'out' => (float) $e->amount]);
        }
        if (! $f->method) {
            foreach ($range(SubcontractorPayment::query()->with('assignment.subcontractor:id,name'), 'date')->get() as $sp) {
                $rows->push(['date' => $sp->date, 'voucher' => 'SUB-'.$sp->id,
                    'particular' => 'Contractor — '.($sp->assignment?->subcontractor?->name ?? '—'), 'method' => '—', 'in' => null, 'out' => (float) $sp->amount]);
            }
            foreach ($range(Payroll::query()->where('status', PayrollStatus::Paid->value)->with('employee:id,name'), 'updated_at')->get() as $pr) {
                $rows->push(['date' => $pr->updated_at, 'voucher' => 'PAYROLL-'.$pr->id,
                    'particular' => 'Salary '.$pr->month.' — '.($pr->employee?->name ?? '—'), 'method' => '—', 'in' => null, 'out' => (float) $pr->net_salary]);
            }
        }

        return $rows;
    }

    public function rows(Filters $f): iterable
    {
        $before = $this->entries($f, true);
        $this->opening = (float) $before->sum('in') - (float) $before->sum('out');

        $balance = $this->opening;

        return $this->entries($f, false)
            ->sortBy(fn ($r) => CarbonImmutable::parse($r['date'])->format('Y-m-d').'|'.(($r['in'] ?? 0) > 0 ? 0 : 1))
            ->values()
            ->map(function ($r) use (&$balance) {
                $balance += ($r['in'] ?? 0) - ($r['out'] ?? 0);

                return $r + ['balance' => $balance];
            });
    }

    public function summary(Collection $rows, array $totals): array
    {
        $closing = $this->opening + ($totals['in'] ?? 0) - ($totals['out'] ?? 0);

        return [
            ['label' => 'Opening balance', 'value' => $this->opening, 'type' => 'money'],
            ['label' => 'Received', 'value' => $totals['in'] ?? 0, 'type' => 'money', 'tone' => 'green'],
            ['label' => 'Paid', 'value' => $totals['out'] ?? 0, 'type' => 'money', 'tone' => 'gold'],
            ['label' => 'Closing balance', 'value' => $closing, 'type' => 'money', 'tone' => $closing < 0 ? 'red' : 'dark'],
        ];
    }
}
