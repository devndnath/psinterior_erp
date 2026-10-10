<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\WarehouseStock;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Stock on hand per product/warehouse, plus a low-stock list. Reuses
 * the exact WHERE-subquery pattern from Products/Index's low-stock
 * filter (Phase 7 got this wrong with HAVING, fixed in commit
 * cf04f8e) — never reintroduced as a HAVING here.
 */
#[Layout('layouts.admin', ['title' => 'Inventory Report'])]
#[Title('Inventory Report')]
class Inventory extends Component
{
    protected function stockByWarehouse()
    {
        return WarehouseStock::query()
            ->with(['product.category', 'warehouse'])
            ->where('quantity', '>', 0)
            ->orderBy('warehouse_id')
            ->get();
    }

    protected function lowStock()
    {
        return Product::query()
            ->with(['category', 'unit'])
            ->withSum('warehouseStocks as total_stock', 'quantity')
            ->whereRaw(
                '(select coalesce(sum(ws.quantity), 0) from warehouse_stocks ws where ws.product_id = products.id) < products.min_stock'
            )
            ->orderBy('name')
            ->get();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $stock = $this->stockByWarehouse();

        return response()->streamDownload(function () use ($stock) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Warehouse', 'Product', 'SKU', 'Quantity']);

            foreach ($stock as $row) {
                fputcsv($out, [$row->warehouse?->name, $row->product?->name, $row->product?->sku, $row->quantity]);
            }

            fclose($out);
        }, 'inventory-report.csv');
    }

    public function render()
    {
        return view('livewire.reports.inventory', [
            'stockByWarehouse' => $this->stockByWarehouse(),
            'lowStock' => $this->lowStock(),
        ]);
    }
}
