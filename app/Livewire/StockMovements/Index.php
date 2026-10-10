<?php

namespace App\Livewire\StockMovements;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * A read-only ledger list — movements are only ever created as a side
 * effect of InventoryService::recordMovement(), so there's no Form or
 * Show for this module, only this Index. Gated at the route-middleware
 * level (`can:view_inventory`, routes/web.php) the same way Profitability
 * is gated on `view_finance`, since there's no model to key a Policy off.
 */
#[Layout('layouts.admin', ['title' => 'Stock Movements'])]
#[Title('Stock Movements')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $product = '';

    #[Url]
    public string $warehouse = '';

    #[Url]
    public string $type = '';

    public function updating($property): void
    {
        if (in_array($property, ['product', 'warehouse', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['product', 'warehouse', 'type']);
    }

    public function render()
    {
        $movements = StockMovement::query()
            ->with(['product', 'warehouse'])
            ->when($this->product, fn ($q) => $q->where('product_id', $this->product))
            ->when($this->warehouse, fn ($q) => $q->where('warehouse_id', $this->warehouse))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(20);

        return view('livewire.stock-movements.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'types' => StockMovementType::options(),
        ]);
    }
}
