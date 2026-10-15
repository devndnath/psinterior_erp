<?php

namespace App\Livewire\MyLeaves;

use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Self-service leave history + "Apply Leave" entry point for ANY staff
 * member with a linked Employee record — deliberately NOT gated by the
 * `view_leaves`/`create_leaves` permissions LeavePolicy enforces for the
 * admin-side Leaves module (those are HR/manager abilities over OTHER
 * people's leave; this is every employee's own). Authorization here is
 * simply "do you have a linked Employee record at all", checked in
 * mount() rather than via Gate — see User::employee()'s docblock, which
 * already anticipated this screen ("a future self-service screen").
 */
#[Layout('layouts.admin', ['title' => 'My Leaves'])]
#[Title('My Leaves')]
class Index extends Component
{
    use WithPagination;

    public Employee $employee;

    #[Url]
    public string $type = '';

    public function mount(): void
    {
        $employee = Auth::user()->employee;

        abort_if(! $employee, 403, 'Your account is not linked to an employee record, so there is no leave history to show.');

        $this->employee = $employee;
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function cancel(int $leaveId): void
    {
        $leave = Leave::where('employee_id', $this->employee->id)->findOrFail($leaveId);

        if ($leave->manager_status->value !== 'pending' || $leave->hr_status->value !== 'pending') {
            $this->dispatch('toast', message: 'This request has already been reviewed and can no longer be cancelled.', type: 'error');

            return;
        }

        $leave->delete();

        $this->dispatch('toast', message: 'Leave request cancelled.', type: 'success');
    }

    public function render()
    {
        $leaves = Leave::where('employee_id', $this->employee->id)
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->latest('from_date')
            ->paginate(15);

        return view('livewire.my-leaves.index', [
            'leaves' => $leaves,
            'types' => LeaveType::options(),
        ]);
    }
}
