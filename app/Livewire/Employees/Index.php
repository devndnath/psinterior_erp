<?php

namespace App\Livewire\Employees;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Employees'])]
#[Title('Employees')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $department = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Employee::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'department', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'department', 'status']);
    }

    public function deleteEmployee(int $employeeId): void
    {
        $employee = Employee::findOrFail($employeeId);
        Gate::authorize('delete', $employee);

        $employee->delete();

        $this->dispatch('toast', message: "Employee {$employee->employee_id} deleted.", type: 'success');
    }

    public function render()
    {
        $employees = Employee::query()
            ->with(['department', 'designation'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('employee_id', 'like', "%{$this->search}%")))
            ->when($this->department, fn ($q) => $q->where('department_id', $this->department))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.employees.index', [
            'employees' => $employees,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'statuses' => EmployeeStatus::options(),
        ]);
    }
}
