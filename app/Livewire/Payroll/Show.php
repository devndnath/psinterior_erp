<?php

namespace App\Livewire\Payroll;

use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Payroll $payroll;

    public function mount(Payroll $payroll): void
    {
        Gate::authorize('view', $payroll);

        $this->payroll = $payroll;
    }

    public function finalize(PayrollService $service): void
    {
        Gate::authorize('finalize', $this->payroll);

        try {
            $this->payroll = $service->finalize($this->payroll);
            $this->dispatch('toast', message: 'Payroll finalized.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function markPaid(PayrollService $service): void
    {
        Gate::authorize('markPaid', $this->payroll);

        try {
            $this->payroll = $service->markPaid($this->payroll);
            $this->dispatch('toast', message: 'Payroll marked paid.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function deletePayroll(): void
    {
        Gate::authorize('delete', $this->payroll);

        $this->payroll->delete();

        $this->dispatch('toast', message: 'Payroll deleted.', type: 'success');
        $this->redirectRoute('payroll.index', navigate: true);
    }

    #[Title('Payroll')]
    public function render()
    {
        $this->payroll->load('employee');

        return view('livewire.payroll.show');
    }
}
