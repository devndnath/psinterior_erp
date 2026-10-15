<?php

namespace App\Livewire\ExpenseClaims;

use App\Models\ExpenseClaim;
use App\Services\ExpenseClaimService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public ExpenseClaim $claim;

    public function mount(ExpenseClaim $expenseClaim): void
    {
        Gate::authorize('view', $expenseClaim);

        $this->claim = $expenseClaim;
    }

    public function approve(ExpenseClaimService $service): void
    {
        Gate::authorize('approve', $this->claim);

        $this->claim = $service->approve($this->claim, Auth::user());

        $this->dispatch('toast', message: 'Expense claim approved.', type: 'success');
    }

    public function reject(ExpenseClaimService $service): void
    {
        Gate::authorize('reject', $this->claim);

        $this->claim = $service->reject($this->claim, Auth::user());

        $this->dispatch('toast', message: 'Expense claim rejected.', type: 'success');
    }

    public function deleteClaim(): void
    {
        Gate::authorize('delete', $this->claim);

        $this->claim->delete();

        $this->dispatch('toast', message: 'Expense claim deleted.', type: 'success');
        $this->redirectRoute('expense-claims.index', navigate: true);
    }

    #[Title('Expense Claim')]
    public function render()
    {
        $this->claim->load(['employee', 'project', 'siteVisit', 'reviewedBy']);

        return view('livewire.expense-claims.show');
    }
}
