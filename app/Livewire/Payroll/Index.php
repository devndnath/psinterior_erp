<?php

namespace App\Livewire\Payroll;

use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Payroll'])]
#[Title('Payroll')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $employee = '';

    #[Url]
    public string $month = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Payroll::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['employee', 'month', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['employee', 'month', 'status']);
    }

    public function render()
    {
        $payrolls = Payroll::query()
            ->with('employee')
            ->when($this->employee, fn ($q) => $q->where('employee_id', $this->employee))
            ->when($this->month, fn ($q) => $q->where('month', $this->month))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('month')
            ->paginate(15);

        return view('livewire.payroll.index', [
            'payrolls' => $payrolls,
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'statuses' => PayrollStatus::options(),
        ]);
    }
}
