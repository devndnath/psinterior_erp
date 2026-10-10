<?php

namespace App\Livewire\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Attendance'])]
#[Title('Attendance')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $employee = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Attendance::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['employee', 'from', 'to', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['employee', 'from', 'to', 'status']);
    }

    public function deleteAttendance(int $attendanceId): void
    {
        $attendance = Attendance::findOrFail($attendanceId);
        Gate::authorize('delete', $attendance);

        $attendance->delete();

        $this->dispatch('toast', message: 'Attendance record deleted.', type: 'success');
    }

    public function render()
    {
        $records = Attendance::query()
            ->with('employee')
            ->when($this->employee, fn ($q) => $q->where('employee_id', $this->employee))
            ->when($this->from, fn ($q) => $q->whereDate('date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('date', '<=', $this->to))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('date')
            ->paginate(20);

        return view('livewire.attendance.index', [
            'records' => $records,
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'employee_id']),
            'statuses' => AttendanceStatus::options(),
        ]);
    }
}
