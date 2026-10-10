<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Expense $expense;

    public function mount(Expense $expense): void
    {
        Gate::authorize('view', $expense);

        $this->expense = $expense;
    }

    public function deleteExpense(): void
    {
        Gate::authorize('delete', $this->expense);

        $no = $this->expense->expense_no;
        $this->expense->delete();

        $this->dispatch('toast', message: "Expense {$no} deleted.", type: 'success');
        $this->redirectRoute('expenses.index', navigate: true);
    }

    #[Title('Expense')]
    public function render()
    {
        $this->expense->load(['project', 'category', 'createdBy']);

        return view('livewire.expenses.show');
    }
}
