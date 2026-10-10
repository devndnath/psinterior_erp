<?php

namespace App\Livewire\Client\ServiceRequests;

use App\Models\Warranty;
use App\Services\ServiceRequestService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Creates a new Service Request against one of the client's OWN
 * project's warranties — the warranty picker is pre-scoped to the
 * client's own projects (ownership check, not a staff Policy — see
 * "Portal authorization model" in PHASE11_SUMMARY.md), and
 * ServiceRequestService::create() is called directly once that scope
 * is applied.
 */
#[Layout('layouts.client-portal', ['title' => 'New Service Request'])]
#[Title('New Service Request')]
class Form extends Component
{
    public string $warrantyId = '';
    public string $complaint = '';

    public function mount(): void
    {
        abort_unless(Auth::guard('client')->user()->client, 403, 'No client record linked to this account.');
    }

    public function save(ServiceRequestService $service): void
    {
        $client = Auth::guard('client')->user()->client;

        $this->validate([
            'warrantyId' => ['required', 'integer'],
            'complaint' => ['required', 'string', 'max:2000'],
        ]);

        $warranty = Warranty::whereHas('project', fn ($q) => $q->where('client_id', $client->id))
            ->findOrFail($this->warrantyId);

        $serviceRequest = $service->create($warranty, [
            'client_id' => $client->id,
            'complaint' => $this->complaint,
        ]);

        $this->dispatch('toast', message: "Service Request {$serviceRequest->sr_no} submitted.", type: 'success');
        $this->redirectRoute('client.service-requests.show', ['serviceRequest' => $serviceRequest->id], navigate: true);
    }

    public function render()
    {
        $client = Auth::guard('client')->user()->client;

        return view('livewire.client.service-requests.form', [
            'warranties' => Warranty::whereHas('project', fn ($q) => $q->where('client_id', $client->id))
                ->with('project')
                ->get(),
        ]);
    }
}
