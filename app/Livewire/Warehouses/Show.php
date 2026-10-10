<?php

namespace App\Livewire\Warehouses;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Warehouse $warehouse;

    public bool $showStockForm = false;
    public string $stock_product_id = '';
    public string $stock_quantity = '';
    public string $stock_mode = 'opening'; // 'opening' or 'adjust'

    public function mount(Warehouse $warehouse): void
    {
        Gate::authorize('view', $warehouse);

        $this->warehouse = $warehouse;
    }

    /**
     * Recording opening stock or an adjustment is a small, non-destructive
     * inline action — gated on being able to manage this warehouse AND
     * holding the distinct `manage_inventory` ability, kept simple with a
     * plain wire:click form (no confirm dialog needed, mirroring how
     * other non-destructive single-step actions in this codebase skip
     * x-confirm-dialog).
     */
    protected function authorizeStockEntry(): void
    {
        Gate::authorize('update', $this->warehouse);

        if (! Auth::user()->can('manage_inventory')) {
            abort(403);
        }
    }

    public function toggleStockForm(string $mode): void
    {
        $this->authorizeStockEntry();

        $closing = $this->showStockForm && $this->stock_mode === $mode;

        $this->stock_mode = $mode;
        $this->showStockForm = ! $closing;
        $this->reset(['stock_product_id', 'stock_quantity']);
    }

    public function recordStockEntry(InventoryService $inventory): void
    {
        $this->authorizeStockEntry();

        $data = $this->validate([
            'stock_product_id' => ['required', 'exists:products,id'],
            'stock_quantity' => ['required', 'numeric', $this->stock_mode === 'opening' ? 'min:0.01' : 'not_in:0'],
        ]);

        $product = Product::findOrFail($data['stock_product_id']);
        $quantity = (float) $data['stock_quantity'];

        try {
            if ($this->stock_mode === 'opening') {
                $inventory->openingStock($product, $this->warehouse, $quantity, Auth::id());
            } else {
                $inventory->adjust($product, $this->warehouse, $quantity, Auth::id());
            }

            $this->reset(['stock_product_id', 'stock_quantity', 'showStockForm']);
            $this->dispatch('toast', message: 'Stock entry recorded.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function deleteWarehouse(): void
    {
        Gate::authorize('delete', $this->warehouse);

        $name = $this->warehouse->name;
        $this->warehouse->delete();

        $this->dispatch('toast', message: "Warehouse \"{$name}\" deleted.", type: 'success');
        $this->redirectRoute('warehouses.index', navigate: true);
    }

    #[Title('Warehouse')]
    public function render()
    {
        return view('livewire.warehouses.show', [
            'stockPerProduct' => $this->warehouse->stocks()->with('product')->get(),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
