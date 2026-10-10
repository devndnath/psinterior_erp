<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Employee $employee;

    public function mount(Employee $employee): void
    {
        Gate::authorize('view', $employee);

        $this->employee = $employee;
    }

    public function deleteEmployee(): void
    {
        Gate::authorize('delete', $this->employee);

        $code = $this->employee->employee_id;
        $this->employee->delete();

        $this->dispatch('toast', message: "Employee {$code} deleted.", type: 'success');
        $this->redirectRoute('employees.index', navigate: true);
    }

    #[Title('Employee')]
    public function render()
    {
        $this->employee->load(['department', 'designation', 'user']);

        return view('livewire.employees.show', [
            'recentAttendance' => $this->employee->attendance()->latest('date')->limit(14)->get(),
            'leaves' => $this->employee->leaves()->latest()->limit(10)->get(),
            'payrolls' => $this->employee->payrolls()->latest('month')->limit(12)->get(),
        ]);
    }
}
