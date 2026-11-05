<?php

namespace App\Reports\Definitions;

use App\Enums\InvoiceStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Reports\Filters;
use App\Reports\Report;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/** VAT charged on invoices against VAT paid on purchases, by month. */
class VatReport extends Report
{
    public static function key(): string { return 'vat'; }
    public static function title(): string { return 'VAT Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_finance'; }
    public static function description(): string { return 'VAT collected on invoices against VAT paid on purchase orders, month by month, and the net payable.'; }

    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'Output VAT = the tax amount on non-cancelled invoices (by invoice date). Input VAT = the tax on sent purchase orders (by order date). Net payable = output − input.';
    }

    public function columns(): array
    {
        return [
            'month' => ['label' => 'Month', 'type' => 'text'],
            'sales' => ['label' => 'Invoiced (incl. VAT)', 'type' => 'money', 'total' => true],
            'output' => ['label' => 'Output VAT', 'type' => 'money', 'total' => true],
            'purchases' => ['label' => 'Purchases (incl. VAT)', 'type' => 'money', 'total' => true],
            'input' => ['label' => 'Input VAT', 'type' => 'money', 'total' => true],
            'net' => ['label' => 'Net payable', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $month = fn ($d) => CarbonImmutable::parse($d)->format('Y-m');
        $inv = $f->dates(Invoice::where('status', '!=', InvoiceStatus::Cancelled->value), 'invoice_date')
            ->get(['invoice_date', 'grand_total', 'tax'])->groupBy(fn ($i) => $month($i->invoice_date));
        $po = $f->dates(PurchaseOrder::whereNotIn('status', [PurchaseOrderStatus::Draft->value, PurchaseOrderStatus::Cancelled->value]), 'date')
            ->get(['date', 'total', 'tax'])->groupBy(fn ($p) => $month($p->date));

        $months = $inv->keys()->merge($po->keys())->unique()->sort();
        foreach ($months as $m) {
            $i = $inv[$m] ?? collect();
            $p = $po[$m] ?? collect();
            $out = (float) $i->sum('tax');
            $in = (float) $p->sum('tax');
            yield [
                'month' => CarbonImmutable::createFromFormat('Y-m-d', $m.'-01')->format('F Y'),
                'sales' => (float) $i->sum('grand_total'),
                'output' => $out,
                'purchases' => (float) $p->sum('total'),
                'input' => $in,
                'net' => $out - $in,
            ];
        }
    }

    public function summary(Collection $rows, array $totals): array
    {
        return [
            ['label' => 'Output VAT', 'value' => $totals['output'] ?? 0, 'type' => 'money', 'tone' => 'green'],
            ['label' => 'Input VAT', 'value' => $totals['input'] ?? 0, 'type' => 'money', 'tone' => 'gold'],
            ['label' => 'Net VAT payable', 'value' => $totals['net'] ?? 0, 'type' => 'money', 'tone' => 'dark'],
        ];
    }
}
