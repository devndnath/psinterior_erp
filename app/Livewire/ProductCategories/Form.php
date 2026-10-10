<?php

namespace App\Livewire\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?ProductCategory $productCategory = null;

    public string $name = '';
    public bool $is_active = true;

    public function mount(?ProductCategory $productCategory = null): void
    {
        $this->productCategory = $productCategory?->exists ? $productCategory : null;

        Gate::authorize($this->productCategory ? 'update' : 'create', $this->productCategory ?? ProductCategory::class);

        if ($this->productCategory) {
            $this->fill([
                'name' => $this->productCategory->name,
                'is_active' => $this->productCategory->is_active,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name,'.($this->productCategory?->id ?: 'NULL')],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->productCategory) {
            $this->productCategory->update($data);
            $message = 'Product category updated.';
        } else {
            ProductCategory::create($data);
            $message = 'Product category created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('product-categories.index', navigate: true);
    }

    #[Title('Product Category')]
    public function render()
    {
        return view('livewire.product-categories.form');
    }
}
