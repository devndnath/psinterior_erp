<?php

namespace App\Livewire\Client\ServiceRequests;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Read-only — status progression stays
 * staff-only (ServiceRequestShow in the admin area).
 */
#[Layout('layouts.client-portal')]
class Show extends Component
{
    public ServiceRequest $serviceRequest;

    public function mount(ServiceRequest $serviceRequest): void
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($serviceRequest->client_id === $client->id, 403);

        $this->serviceRequest = $serviceRequest;
    }

    #[Title('Service Request')]
    public function render()
    {
        $this->serviceRequest->load(['warranty.project', 'assignedTechnician']);

        return view('livewire.client.service-requests.show');
    }
}
