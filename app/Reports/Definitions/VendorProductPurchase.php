<?php

namespace App\Reports\Definitions;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrderItem;
use App\Reports\Filters;
use App\Reports\Report;

/** Products bought from each vendor. */
class VendorProductPurchase extends Report
{
    public static function key(): string { return 'vendor-product-purchase'; }
    public static function title(): string { return 'Vendor Wise Product Purchase'; }
    public static function group(): string { return 'all'; }
    public static function permission(): string { return 'view_purchase_orders'; }
    public static function description(): string { return 'Products bought from each vendor — quantity, average rate and value.'; }

    public function filters(): array { return ['dates', 'vendor']; }
    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'From sent purchase orders (drafts and cancelled left out), by order date. Average rate = amount ÷ quantity.';
    }

    public function columns(): array
    {
        return [
            'vendor' => ['label' => 'Vendor', 'type' => 'text'],
            'product' => ['label' => 'Product', 'type' => 'text'],
            'qty' => ['label' => 'Quantity', 'type' => 'qty'],
            'rate' => ['label' => 'Avg. rate', 'type' => 'money'],
            'amount' => ['label' => 'Amount', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $items = PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $f->dates($q, 'date')
                ->whereNotIn('status', [PurchaseOrderStatus::Draft->value, PurchaseOrderStatus::Cancelled->value])
                ->when($f->vendor, fn ($q) => $q->where('vendor_id', $f->vendor)))
            ->with(['purchaseOrder:id,vendor_id', 'purchaseOrder.vendor:id,company_name', 'product:id,name,unit_id', 'product.unit:id,short_code'])
            ->get();

        $groups = $items->groupBy(fn ($i) => $i->purchaseOrder->vendor_id.'|'.$i->product_id)
            ->sortBy(fn ($g) => ($g->first()->purchaseOrder->vendor?->company_name ?? '').$g->first()->product?->name);

        foreach ($groups as $g) {
            $first = $g->first();
            $vendor = $first->purchaseOrder->vendor;
            $qty = (float) $g->sum('qty');
            $amount = (float) $g->sum('amount');
            yield [
                'vendor' => $vendor ? self::cell($vendor->company_name, route('vendors.show', $vendor)) : '—',
                'product' => self::cell($first->product?->name ?? '—', null, $first->product?->unit?->short_code),
                'qty' => $qty,
                'rate' => $qty > 0 ? round($amount / $qty, 2) : 0,
                'amount' => $amount,
            ];
        }
    }
}
