<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public string $sku = '';
    public string $name = '';
    public string $category_id = '';
    public string $brand = '';
    public string $unit_id = '';
    public string $purchase_price = '0';
    public string $usage_rate = '';
    public string $min_stock = '0';
    public string $description = '';
    public $image;

    public function mount(?Product $product = null): void
    {
        $this->product = $product?->exists ? $product : null;

        Gate::authorize($this->product ? 'update' : 'create', $this->product ?? Product::class);

        if ($this->product) {
            $this->fill([
                'sku' => $this->product->sku,
                'name' => $this->product->name,
                'category_id' => (string) $this->product->category_id,
                'brand' => (string) $this->product->brand,
                'unit_id' => (string) $this->product->unit_id,
                'purchase_price' => (string) $this->product->purchase_price,
                'usage_rate' => (string) $this->product->usage_rate,
                'min_stock' => (string) $this->product->min_stock,
                'description' => (string) $this->product->description,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.($this->product?->id ?: 'NULL')],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['required', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'usage_rate' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        unset($data['image']);

        $data['usage_rate'] = $data['usage_rate'] ?: null;
        $data['brand'] = $data['brand'] ?: null;
        $data['description'] = $data['description'] ?: null;

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->product) {
            $this->product->update($data);
            $message = "Product {$this->product->sku} updated.";
        } else {
            $this->product = Product::create($data);
            $message = "Product {$this->product->sku} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('products.show', ['product' => $this->product->id], navigate: true);
    }

    #[Title('Product')]
    public function render()
    {
        return view('livewire.products.form', [
            'categories' => ProductCategory::active()->orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }
}
