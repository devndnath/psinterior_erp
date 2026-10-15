<?php

namespace App\Livewire\MyExpenseClaims;

use App\Enums\ExpenseClaimStatus;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Self-service "My Expense Claims" — submit and track your own
 * conveyance/meal/accommodation reimbursement claims. Same
 * authorization split as MyLeaves\Index: gated on "do you have a linked
 * Employee record", not on ExpenseClaimPolicy's `view_expense_claims`
 * (that permission is for HR/Accounts reviewing OTHER people's claims).
 */
#[Layout('layouts.admin', ['title' => 'My Expense Claims'])]
#[Title('My Expense Claims')]
class Index extends Component
{
    use WithPagination;

    public Employee $employee;

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        $employee = Auth::user()->employee;

        abort_if(! $employee, 403, 'Your account is not linked to an employee record, so there is no claim history to show.');

        $this->employee = $employee;
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function cancel(int $claimId): void
    {
        $claim = ExpenseClaim::where('employee_id', $this->employee->id)->findOrFail($claimId);

        if (! $claim->isPending()) {
            $this->dispatch('toast', message: 'This claim has already been reviewed and can no longer be cancelled.', type: 'error');

            return;
        }

        $claim->delete();

        $this->dispatch('toast', message: 'Expense claim cancelled.', type: 'success');
    }

    public function render()
    {
        $claims = ExpenseClaim::where('employee_id', $this->employee->id)
            ->with(['project', 'siteVisit'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('date')
            ->paginate(15);

        return view('livewire.my-expense-claims.index', [
            'claims' => $claims,
            'statuses' => ExpenseClaimStatus::options(),
        ]);
    }
}
