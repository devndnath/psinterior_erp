<?php

namespace App\Reports\Definitions;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrderItem;
use App\Reports\Filters;
use App\Reports\Report;

/** Products bought for each project (purchase orders). */
class ProjectProductPurchase extends Report
{
    public static function key(): string { return 'project-product-purchase'; }
    public static function title(): string { return 'Project Wise Product Purchase'; }
    public static function group(): string { return 'all'; }
    public static function description(): string { return 'Products ordered for each project — quantity ordered and received, and the value.'; }

    public function filters(): array { return ['dates', 'project']; }
    public function defaultRange(): string { return 'year'; }

    public function note(): ?string
    {
        return 'From purchase orders that have been sent (drafts and cancelled orders are left out), dated by the order date. Received = accepted quantity on goods receipts.';
    }

    public function columns(): array
    {
        return [
            'project' => ['label' => 'Project', 'type' => 'text'],
            'product' => ['label' => 'Product', 'type' => 'text'],
            'orders' => ['label' => 'Orders', 'type' => 'int'],
            'qty' => ['label' => 'Qty ordered', 'type' => 'qty'],
            'received' => ['label' => 'Qty received', 'type' => 'qty'],
            'amount' => ['label' => 'Amount', 'type' => 'money', 'total' => true],
        ];
    }

    public function rows(Filters $f): iterable
    {
        $items = PurchaseOrderItem::query()
            ->whereHas('purchaseOrder', fn ($q) => $f->dates($q, 'date')
                ->whereNotIn('status', [PurchaseOrderStatus::Draft->value, PurchaseOrderStatus::Cancelled->value])
                ->when($f->project, fn ($q) => $q->where('project_id', $f->project)))
            ->with(['purchaseOrder:id,project_id,po_no', 'purchaseOrder.project:id,project_code,name', 'product:id,name,unit_id', 'product.unit:id,short_code'])
            ->withSum('goodsReceiveItems as received_qty', 'accepted_qty')
            ->get();

        $groups = $items->groupBy(fn ($i) => $i->purchaseOrder->project_id.'|'.$i->product_id)
            ->sortBy(fn ($g) => ($g->first()->purchaseOrder->project?->project_code ?? '').$g->first()->product?->name);

        foreach ($groups as $g) {
            $first = $g->first();
            $project = $first->purchaseOrder->project;
            $unit = $first->product?->unit?->short_code;
            yield [
                'project' => $project ? self::cell($project->project_code, route('projects.show', $project), $project->name) : '—',
                'product' => self::cell($first->product?->name ?? '—', null, $unit),
                'orders' => $g->pluck('purchase_order_id')->unique()->count(),
                'qty' => (float) $g->sum('qty'),
                'received' => (float) $g->sum('received_qty'),
                'amount' => (float) $g->sum('amount'),
            ];
        }
    }
}
