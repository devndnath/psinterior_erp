<?php

namespace App\Livewire\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Reached either standalone (create, with an employee select) or via
 * `?employee={id}` the way ChangeOrders/Form is reached via
 * `?project=` — same dual-entry-point convention — or as an edit of an
 * existing record.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Attendance $attendance = null;

    public string $employee_id = '';
    public string $date = '';
    public string $check_in = '';
    public string $check_out = '';
    public string $status = 'present';

    public function mount(?Attendance $attendance = null): void
    {
        $this->attendance = $attendance?->exists ? $attendance : null;

        Gate::authorize($this->attendance ? 'update' : 'create', $this->attendance ?? Attendance::class);

        if ($this->attendance) {
            $this->fill([
                'employee_id' => (string) $this->attendance->employee_id,
                'date' => $this->attendance->date->toDateString(),
                'check_in' => optional($this->attendance->check_in)->format('H:i') ?? '',
                'check_out' => optional($this->attendance->check_out)->format('H:i') ?? '',
                'status' => $this->attendance->status->value,
            ]);
        } else {
            $this->date = now()->toDateString();

            $employeeId = request()->query('employee');

            if ($employeeId && Employee::whereKey($employeeId)->exists()) {
                $this->employee_id = (string) $employeeId;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(AttendanceStatus::cases(), 'value'))],
        ];
    }

    public function save(AttendanceService $service): void
    {
        $data = $this->validate();

        $payload = [
            'employee_id' => $data['employee_id'],
            'date' => $data['date'],
            'check_in' => $data['check_in'] ? "{$data['date']} {$data['check_in']}:00" : null,
            'check_out' => $data['check_out'] ? "{$data['date']} {$data['check_out']}:00" : null,
            'status' => $data['status'],
        ];

        if ($this->attendance) {
            $service->update($this->attendance, $payload);
        } else {
            // updateOrCreate inside the service respects the
            // (employee_id, date) uniqueness constraint rather than
            // risking a duplicate-key error if one already exists.
            $service->create($payload);
        }

        $this->dispatch('toast', message: 'Attendance recorded.', type: 'success');
        $this->redirectRoute('attendance.index', navigate: true);
    }

    #[Title('Attendance')]
    public function render()
    {
        return view('livewire.attendance.form', [
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'statuses' => AttendanceStatus::options(),
        ]);
    }
}
