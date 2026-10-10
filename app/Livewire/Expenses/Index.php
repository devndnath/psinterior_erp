<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Expenses'])]
#[Title('Expenses')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Expense::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'category', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'project']);
    }

    public function deleteExpense(int $expenseId): void
    {
        $expense = Expense::findOrFail($expenseId);
        Gate::authorize('delete', $expense);

        $expense->delete();

        $this->dispatch('toast', message: "Expense {$expense->expense_no} deleted.", type: 'success');
    }

    public function render()
    {
        $expenses = Expense::query()
            ->with(['project', 'category'])
            ->when($this->search, fn ($q) => $q->where('expense_no', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest('date')
            ->paginate(15);

        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
