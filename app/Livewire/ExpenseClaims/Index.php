<?php

namespace App\Livewire\ExpenseClaims;

use App\Enums\ExpenseClaimStatus;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin/HR/Accounts-side listing of every employee's expense claims —
 * the review queue. Gated on `view_expense_claims` (hr-manager,
 * accountant — see RolePermissionSeeder), distinct from the self-service
 * My Expense Claims screens.
 */
#[Layout('layouts.admin', ['title' => 'Expense Claims'])]
#[Title('Expense Claims')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $employee = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ExpenseClaim::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['employee', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['employee', 'status']);
    }

    public function render()
    {
        $claims = ExpenseClaim::query()
            ->with('employee')
            ->when($this->employee, fn ($q) => $q->where('employee_id', $this->employee))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('date')
            ->paginate(15);

        return view('livewire.expense-claims.index', [
            'claims' => $claims,
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'statuses' => ExpenseClaimStatus::options(),
        ]);
    }
}
