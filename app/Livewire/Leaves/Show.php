<?php

namespace App\Livewire\Leaves;

use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Leave $leave;

    public function mount(Leave $leave): void
    {
        Gate::authorize('view', $leave);

        $this->leave = $leave;
    }

    public function approveByManager(LeaveService $service): void
    {
        Gate::authorize('approveByManager', $this->leave);

        $this->leave = $service->approveByManager($this->leave);

        $this->dispatch('toast', message: 'Approved at manager stage.', type: 'success');
    }

    public function approveByHr(LeaveService $service): void
    {
        Gate::authorize('approveByHr', $this->leave);

        $this->leave = $service->approveByHr($this->leave);

        $this->dispatch('toast', message: 'Approved at HR stage.', type: 'success');
    }

    public function rejectByManager(LeaveService $service): void
    {
        Gate::authorize('reject', $this->leave);

        $this->leave = $service->reject($this->leave, 'manager');

        $this->dispatch('toast', message: 'Rejected at manager stage.', type: 'success');
    }

    public function rejectByHr(LeaveService $service): void
    {
        Gate::authorize('reject', $this->leave);

        $this->leave = $service->reject($this->leave, 'hr');

        $this->dispatch('toast', message: 'Rejected at HR stage.', type: 'success');
    }

    #[Title('Leave Request')]
    public function render()
    {
        $this->leave->load('employee');

        return view('livewire.leaves.show');
    }
}
