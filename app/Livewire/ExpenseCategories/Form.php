<?php

namespace App\Livewire\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?ExpenseCategory $expenseCategory = null;

    public string $name = '';
    public bool $is_active = true;

    public function mount(?ExpenseCategory $expenseCategory = null): void
    {
        $this->expenseCategory = $expenseCategory?->exists ? $expenseCategory : null;

        Gate::authorize($this->expenseCategory ? 'update' : 'create', $this->expenseCategory ?? ExpenseCategory::class);

        if ($this->expenseCategory) {
            $this->fill([
                'name' => $this->expenseCategory->name,
                'is_active' => $this->expenseCategory->is_active,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name,'.($this->expenseCategory?->id ?: 'NULL')],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->expenseCategory) {
            $this->expenseCategory->update($data);
            $message = 'Expense category updated.';
        } else {
            ExpenseCategory::create($data);
            $message = 'Expense category created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('expense-categories.index', navigate: true);
    }

    #[Title('Expense Category')]
    public function render()
    {
        return view('livewire.expense-categories.form');
    }
}
