<?php

namespace App\Livewire\ServiceRequests;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceRequestService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public ServiceRequest $serviceRequest;

    public string $assign_user_id = '';

    public function mount(ServiceRequest $serviceRequest): void
    {
        Gate::authorize('view', $serviceRequest);

        $this->serviceRequest = $serviceRequest;
        $this->assign_user_id = (string) $serviceRequest->assigned_technician;
    }

    public function assignTechnician(ServiceRequestService $service): void
    {
        Gate::authorize('assignTechnician', $this->serviceRequest);

        $this->validate(['assign_user_id' => ['required', 'exists:users,id']]);

        try {
            $this->serviceRequest = $service->assignTechnician($this->serviceRequest, (int) $this->assign_user_id);
            $this->dispatch('toast', message: "Service request {$this->serviceRequest->sr_no} assigned.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function scheduleSiteVisit(ServiceRequestService $service): void
    {
        Gate::authorize('scheduleSiteVisit', $this->serviceRequest);

        try {
            $this->serviceRequest = $service->scheduleSiteVisit($this->serviceRequest);
            $this->dispatch('toast', message: 'Site visit scheduled.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function markRepair(ServiceRequestService $service): void
    {
        Gate::authorize('markRepair', $this->serviceRequest);

        try {
            $this->serviceRequest = $service->markRepair($this->serviceRequest);
            $this->dispatch('toast', message: 'Marked under repair.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function markVerification(ServiceRequestService $service): void
    {
        Gate::authorize('markVerification', $this->serviceRequest);

        try {
            $this->serviceRequest = $service->markVerification($this->serviceRequest);
            $this->dispatch('toast', message: 'Moved to verification.', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function close(ServiceRequestService $service): void
    {
        Gate::authorize('close', $this->serviceRequest);

        try {
            $this->serviceRequest = $service->close($this->serviceRequest);
            $this->dispatch('toast', message: "Service request {$this->serviceRequest->sr_no} closed.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    #[Title('Service Request')]
    public function render()
    {
        $this->serviceRequest->load(['warranty.project', 'client', 'assignedTechnician']);

        return view('livewire.service-requests.show', [
            'users' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
