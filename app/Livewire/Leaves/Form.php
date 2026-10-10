<?php

namespace App\Livewire\Leaves;

use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public string $employee_id = '';
    public string $type = 'casual';
    public string $from_date = '';
    public string $to_date = '';
    public string $reason = '';

    public function mount(): void
    {
        Gate::authorize('create', Leave::class);

        $this->from_date = now()->toDateString();
        $this->to_date = now()->toDateString();

        $employeeId = request()->query('employee');

        if ($employeeId && Employee::whereKey($employeeId)->exists()) {
            $this->employee_id = (string) $employeeId;
        }
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(LeaveType::cases(), 'value'))],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(LeaveService $service): void
    {
        $data = $this->validate();

        $employee = Employee::findOrFail($data['employee_id']);
        $leave = $service->create($employee, $data);

        $this->dispatch('toast', message: 'Leave request submitted.', type: 'success');
        $this->redirectRoute('leaves.show', ['leave' => $leave->id], navigate: true);
    }

    #[Title('New Leave Request')]
    public function render()
    {
        return view('livewire.leaves.form', [
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'types' => LeaveType::options(),
        ]);
    }
}
