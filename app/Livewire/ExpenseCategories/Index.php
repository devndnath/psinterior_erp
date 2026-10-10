<?php

namespace App\Livewire\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin-editable reference list (same spirit as Settings) — gated
 * entirely on `manage_settings`. Simpler than the other modules: no Show
 * page, just Index + Form.
 */
#[Layout('layouts.admin', ['title' => 'Expense Categories'])]
#[Title('Expense Categories')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', ExpenseCategory::class);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = ExpenseCategory::findOrFail($categoryId);
        Gate::authorize('delete', $category);

        $category->delete();

        $this->dispatch('toast', message: "Expense category \"{$category->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.expense-categories.index', [
            'categories' => ExpenseCategory::orderBy('name')->paginate(15),
        ]);
    }
}
