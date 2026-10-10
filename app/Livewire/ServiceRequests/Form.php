<?php

namespace App\Livewire\ServiceRequests;

use App\Models\Client;
use App\Models\ServiceRequest;
use App\Models\Warranty;
use App\Services\ServiceRequestService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Always reached from a Warranty's Show page (`?warranty={id}`) — no
 * Index, the same context-only access pattern as Handovers/Form.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public Warranty $warranty;

    public string $client_id = '';
    public string $complaint = '';

    public function mount(): void
    {
        Gate::authorize('create', ServiceRequest::class);

        $this->warranty = Warranty::with('project')->findOrFail(request()->query('warranty'));

        // Pre-fill from the warranty's project's client, mirroring how
        // QuotationForm derives client_id from project.
        if ($this->warranty->project?->client_id) {
            $this->client_id = (string) $this->warranty->project->client_id;
        }
    }

    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'complaint' => ['required', 'string', 'max:2000'],
        ];
    }

    public function save(ServiceRequestService $service): void
    {
        $data = $this->validate();

        $sr = $service->create($this->warranty, $data);

        $this->dispatch('toast', message: "Service request {$sr->sr_no} created.", type: 'success');
        $this->redirectRoute('service-requests.show', ['serviceRequest' => $sr->id], navigate: true);
    }

    #[Title('New Service Request')]
    public function render()
    {
        return view('livewire.service-requests.form', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
