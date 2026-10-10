<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        Gate::authorize('view', $product);

        $this->product = $product;
    }

    public function deleteProduct(): void
    {
        Gate::authorize('delete', $this->product);

        $name = $this->product->name;
        $this->product->delete();

        $this->dispatch('toast', message: "Product \"{$name}\" deleted.", type: 'success');
        $this->redirectRoute('products.index', navigate: true);
    }

    #[Title('Product')]
    public function render()
    {
        $this->product->load(['category', 'unit']);

        return view('livewire.products.show', [
            'stockPerWarehouse' => $this->product->warehouseStocks()->with('warehouse')->get(),
            'recentMovements' => $this->product->stockMovements()->with('warehouse')->latest()->limit(20)->get(),
            'totalStock' => $this->product->totalStock(),
        ]);
    }
}
