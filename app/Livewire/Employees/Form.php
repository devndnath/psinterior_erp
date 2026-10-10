<?php

namespace App\Livewire\Employees;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Employee $employee = null;

    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $department_id = '';
    public string $designation_id = '';
    public string $joining_date = '';
    public string $salary = '0';
    public string $employment_type = 'full_time';
    public string $emergency_contact = '';
    public string $status = 'active';

    public function mount(?Employee $employee = null): void
    {
        $this->employee = $employee?->exists ? $employee : null;

        Gate::authorize($this->employee ? 'update' : 'create', $this->employee ?? Employee::class);

        if ($this->employee) {
            $this->fill([
                'name' => $this->employee->name,
                'phone' => (string) $this->employee->phone,
                'email' => (string) $this->employee->email,
                'address' => (string) $this->employee->address,
                'department_id' => (string) $this->employee->department_id,
                'designation_id' => (string) $this->employee->designation_id,
                'joining_date' => $this->employee->joining_date?->toDateString() ?? '',
                'salary' => (string) $this->employee->salary,
                'employment_type' => $this->employee->employment_type->value,
                'emergency_contact' => (string) $this->employee->emergency_contact,
                'status' => $this->employee->status->value,
            ]);
        } else {
            $this->joining_date = now()->toDateString();
        }
    }

    /**
     * Dependent select: the designation dropdown is filtered to the
     * currently-chosen department. No existing form in this codebase
     * has a dependent-select pair yet, so this is a new pattern — kept
     * simple with a plain helper re-evaluated on render() each time
     * wire:model.live="department_id" changes, the same "plain helper,
     * not #[Computed]" convention Payments/Form.php's
     * availableInvoices() already uses, rather than a dedicated
     * JS/Alpine component.
     */
    protected function designationOptions()
    {
        return Designation::query()
            ->when($this->department_id, fn ($q) => $q->where('department_id', $this->department_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedDepartmentId(): void
    {
        if ($this->designation_id && ! Designation::where('id', $this->designation_id)->where('department_id', $this->department_id)->exists()) {
            $this->designation_id = '';
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'joining_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'employment_type' => ['required', 'string', 'in:'.implode(',', array_column(EmploymentType::cases(), 'value'))],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(EmployeeStatus::cases(), 'value'))],
        ];
    }

    public function save(EmployeeService $service): void
    {
        $data = $this->validate();

        $data['department_id'] = $data['department_id'] ?: null;
        $data['designation_id'] = $data['designation_id'] ?: null;

        if ($this->employee) {
            $employee = $service->update($this->employee, $data);
            $message = "Employee {$employee->employee_id} updated.";
        } else {
            $employee = $service->create($data);
            $message = "Employee {$employee->employee_id} created.";
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('employees.show', ['employee' => $employee->id], navigate: true);
    }

    #[Title('Employee')]
    public function render()
    {
        return view('livewire.employees.form', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'designations' => $this->designationOptions(),
            'employmentTypes' => EmploymentType::options(),
            'statuses' => EmployeeStatus::options(),
        ]);
    }
}
