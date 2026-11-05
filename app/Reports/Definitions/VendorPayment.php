<?php

namespace App\Reports\Definitions;

use App\Enums\PurchaseOrderStatus;
use App\Enums\VendorInvoiceStatus;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\VendorInvoice;
use App\Reports\Filters;
use App\Reports\Report;

/** What each vendor has supplied and been paid. */
class VendorPayment extends Report
{
    public static function key(): string { return 'vendor-payment'; }
    public static function title(): string { return 'Vendor Wise Payment Report'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_vendors'; }
    public static function description(): string { return 'Each vendor — purchase orders placed, paid so far, bills awaiting approval and the balance.'; }

    public function filters(): array { return ['dates', 'vendor']; }
    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'Purchased = total of sent purchase orders (by order date). Paid = expenses recorded against the vendor (by expense date). Balance = purchased − paid.';
    }

    public function columns(): array
    {
        return [
            'vendor' => ['label' => 'Vendor', 'type' => 'text'],
            'orders' => ['label' => 'Orders', 'type' => 'int', 'total' => true],
            'purchased' => ['label' => 'Purchased', 'type' => 'money', 'total' => true],
            'paid' => ['label' => 'Paid', 'type' => 'money', 'total' => true],
            'bills' => ['label' => 'Bills awaiting', 'type' => 'money', 'total' => true],
            'balance' => ['label' => 'Balance', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $po = $f->dates(PurchaseOrder::whereNotIn('status', [PurchaseOrderStatus::Draft->value, PurchaseOrderStatus::Cancelled->value]), 'date')
            ->selectRaw('vendor_id, COUNT(*) as n, SUM(total) as v')->groupBy('vendor_id')->get()->keyBy('vendor_id');
        $paid = $f->dates(Expense::whereNotNull('vendor_id'), 'date')
            ->selectRaw('vendor_id, SUM(amount) as v')->groupBy('vendor_id')->pluck('v', 'vendor_id');
        $bills = VendorInvoice::where('status', VendorInvoiceStatus::Submitted->value)
            ->selectRaw('vendor_id, SUM(amount) as v')->groupBy('vendor_id')->pluck('v', 'vendor_id');

        $vendors = Vendor::query()->when($f->vendor, fn ($q) => $q->whereKey($f->vendor))
            ->whereIn('id', $po->keys()->merge($paid->keys())->merge($bills->keys())->unique())
            ->orderBy('company_name')->get();

        foreach ($vendors as $v) {
            $purchased = (float) ($po[$v->id]->v ?? 0);
            $p = (float) ($paid[$v->id] ?? 0);
            yield [
                'vendor' => self::cell($v->company_name, route('vendors.show', $v), $v->phone),
                'orders' => (int) ($po[$v->id]->n ?? 0),
                'purchased' => $purchased,
                'paid' => $p,
                'bills' => (float) ($bills[$v->id] ?? 0),
                'balance' => $purchased - $p,
            ];
        }
    }
}
