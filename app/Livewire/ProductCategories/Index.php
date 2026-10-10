<?php

namespace App\Livewire\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin-editable reference list (same spirit as ExpenseCategories) —
 * gated entirely on `manage_settings`. No Show page, just Index + Form.
 */
#[Layout('layouts.admin', ['title' => 'Product Categories'])]
#[Title('Product Categories')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', ProductCategory::class);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = ProductCategory::findOrFail($categoryId);
        Gate::authorize('delete', $category);

        $category->delete();

        $this->dispatch('toast', message: "Product category \"{$category->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.product-categories.index', [
            'categories' => ProductCategory::orderBy('name')->paginate(15),
        ]);
    }
}
