<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Products'])]
#[Title('Products')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public bool $lowStock = false;

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'category', 'lowStock'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'lowStock']);
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        Gate::authorize('delete', $product);

        $product->delete();

        $this->dispatch('toast', message: "Product \"{$product->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        $products = Product::query()
            ->with(['category', 'unit'])
            // Aggregates each product's total stock across warehouses in
            // one subquery rather than N+1-ing warehouse_stocks per row.
            ->withSum('warehouseStocks as total_stock', 'quantity')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('sku', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%")
            ))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->lowStock, fn ($q) => $q->havingRaw('COALESCE(total_stock, 0) < min_stock'))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => ProductCategory::active()->orderBy('name')->get(),
        ]);
    }
}
