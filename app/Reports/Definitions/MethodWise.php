<?php

namespace App\Reports\Definitions;

use App\Enums\PaymentMethod;
use App\Models\Expense;
use App\Models\Payment;
use App\Reports\Filters;
use App\Reports\Report;

/** Money in and out per payment method (cash, bank, bKash…). */
class MethodWise extends Report
{
    public static function key(): string { return 'method-wise'; }
    public static function title(): string { return 'Method Wise Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'Received and paid per payment method — cash, bank, bKash, Nagad, cheque and more.'; }

    public function note(): ?string
    {
        return 'Received = client payments; paid = expenses. Contractor payments and salaries carry no method and are not included.';
    }

    public function columns(): array
    {
        return [
            'method' => ['label' => 'Method', 'type' => 'text'],
            'received_n' => ['label' => 'Receipts', 'type' => 'int', 'total' => true],
            'received' => ['label' => 'Received', 'type' => 'money', 'total' => true],
            'paid_n' => ['label' => 'Payments', 'type' => 'int', 'total' => true],
            'paid' => ['label' => 'Paid', 'type' => 'money', 'total' => true],
            'net' => ['label' => 'Net', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $in = $f->dates(Payment::query(), 'date')->selectRaw('method, COUNT(*) as n, SUM(amount) as v')->groupBy('method')->get()->keyBy(fn ($r) => $r->getRawOriginal('method'));
        $out = $f->dates(Expense::query(), 'date')->selectRaw('method, COUNT(*) as n, SUM(amount) as v')->groupBy('method')->get()->keyBy(fn ($r) => $r->getRawOriginal('method'));

        foreach (PaymentMethod::cases() as $m) {
            $i = $in[$m->value] ?? null;
            $o = $out[$m->value] ?? null;
            if (! $i && ! $o) {
                continue;
            }
            yield [
                'method' => $m->label(),
                'received_n' => (int) ($i->n ?? 0),
                'received' => (float) ($i->v ?? 0),
                'paid_n' => (int) ($o->n ?? 0),
                'paid' => (float) ($o->v ?? 0),
                'net' => (float) ($i->v ?? 0) - (float) ($o->v ?? 0),
            ];
        }
    }
}
