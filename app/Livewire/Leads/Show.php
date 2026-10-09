<?php

namespace App\Livewire\Leads;

use App\Enums\FollowupType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Lead $lead;

    // Follow-up quick-add form
    public string $followup_date = '';
    public string $followup_time = '';
    public string $followup_type = 'call';
    public string $followup_notes = '';
    public string $followup_next_date = '';
    public string $followup_assigned_to = '';

    public function mount(Lead $lead): void
    {
        Gate::authorize('view', $lead);
        $this->lead = $lead;
        $this->followup_date = now()->format('Y-m-d');
        $this->followup_assigned_to = (string) ($lead->assigned_to ?? '');
    }

    public function addFollowup(LeadService $service): void
    {
        Gate::authorize('update', $this->lead);

        $data = $this->validate([
            'followup_date' => ['required', 'date'],
            'followup_time' => ['nullable', 'date_format:H:i'],
            'followup_type' => ['required', 'string', 'in:'.implode(',', array_column(FollowupType::cases(), 'value'))],
            'followup_notes' => ['nullable', 'string', 'max:2000'],
            'followup_next_date' => ['nullable', 'date', 'after_or_equal:followup_date'],
            'followup_assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $service->addFollowup($this->lead, [
            'date' => $data['followup_date'],
            'time' => $data['followup_time'] ?: null,
            'type' => $data['followup_type'],
            'notes' => $data['followup_notes'] ?: null,
            'next_followup_date' => $data['followup_next_date'] ?: null,
            'assigned_to' => $data['followup_assigned_to'] ?: null,
        ]);

        $this->reset(['followup_notes', 'followup_next_date']);
        $this->followup_date = now()->format('Y-m-d');
        $this->lead->refresh();

        $this->dispatch('toast', message: 'Follow-up logged.', type: 'success');
    }

    public function markFollowupDone(int $followupId): void
    {
        Gate::authorize('update', $this->lead);

        $followup = $this->lead->followups()->findOrFail($followupId);
        $followup->update(['status' => \App\Enums\FollowupStatus::Done]);
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
        return view('livewire.leads.show', [
            'followups' => $this->lead->followups,
            'statuses' => LeadStatus::options(),
        ]);
    }
}
