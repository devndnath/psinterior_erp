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

    /**
     * See App\Http\Controllers\Pdf\InvoicePdfController's docblock — same
     * fix, same reason: a binary PDF can't safely travel through
     * Livewire's own JSON response, so this redirects (full browser
     * navigation, not `navigate: true`) to a plain GET route instead.
     * The blade's "Print" button links straight to the same route with
     * `?print=1` (see Pdf\Concerns\RespondsWithPdf) and doesn't need a
     * Livewire action at all, since it opens in a new tab.
     */
    public function downloadPdf()
    {
        Gate::authorize('view', $this->expense);

        return $this->redirect(route('expenses.pdf', $this->expense));
    }

    #[Title('Expense')]
    public function render()
    {
        $this->expense->load(['project', 'category', 'createdBy']);

        return view('livewire.expenses.show');
    }
}
