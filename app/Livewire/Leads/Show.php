<?php

namespace App\Livewire\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Lead $lead;

    public function mount(Lead $lead): void
    {
        Gate::authorize('view', $lead);
        $this->lead = $lead;
    }

    /** The call card (Calls\Panel) changed something — refresh "Next call". */
    #[On('calls-updated')]
    public function refreshCalls(): void
    {
        $this->lead->refresh();
    }

    public function convertToClient(LeadService $service): void
    {
        Gate::authorize('convert', $this->lead);

        $client = $service->convertToClient($this->lead);

        $this->dispatch('toast', message: "Converted to client {$client->client_no}.", type: 'success');
        $this->redirectRoute('clients.show', ['client' => $client->id], navigate: true);
    }

    #[Title('Lead')]
    public function render()
    {
        $this->lead->loadMissing(['referenceClient', 'referenceEmployee']);

        return view('livewire.leads.show', [
            'nextCall' => $this->lead->followups()->where('status', \App\Enums\FollowupStatus::Pending->value)
                ->reorder()->orderBy('date')->orderBy('time')->with('assignee:id,name')->first(),
            'statuses' => LeadStatus::options(),
        ]);
    }
}
