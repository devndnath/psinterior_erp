<?php

namespace App\Livewire\Calls;

use App\Enums\CallOutcome;
use App\Enums\FollowupStatus;
use App\Enums\FollowupType;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use App\Services\CallService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * "Calls & follow-ups" card on a lead's or a client's page: one-tap
 * call / WhatsApp, the calls scheduled for them, their history, and a
 * form to log or schedule one. It reads and writes the same rows as the
 * Calls page and the CRM overview (via CallService), so all of them stay
 * in sync. A client's card also lists the calls carried over from the
 * lead it was converted from.
 */
class Panel extends Component
{
    public ?Lead $lead = null;
    public ?Client $client = null;

    // Log / schedule form.
    public bool $showForm = false;
    public string $mode = 'done'; // done = log a call made now; pending = schedule one
    public string $type = 'call';
    public string $date = '';
    public string $time = '';
    public string $outcome = '';
    public string $notes = '';
    public string $next_date = '';
    public string $assigned_to = '';

    // Finishing a scheduled call.
    public ?int $completing = null;
    public string $complete_outcome = 'talked';
    public string $complete_result = '';
    public string $complete_next_date = '';

    /**
     * Livewire fills $lead / $client from the parameters passed in
     * (<livewire:calls.panel :client="$client" />). No mount() parameters
     * on purpose: a type-hinted model parameter that isn't passed gets a
     * blank instance from the container rather than null.
     */
    public function mount(): void
    {
        abort_unless($this->lead?->exists || $this->client?->exists, 404);
        Gate::authorize('view', $this->contact());

        $this->date = now()->format('Y-m-d');
        $this->time = now()->format('H:i');
        $this->assigned_to = (string) ($this->contact()->assigned_to ?? Auth::id() ?? '');
    }

    private function contact(): Lead|Client
    {
        return $this->lead ?? $this->client;
    }

    public function openForm(string $mode = 'done'): void
    {
        $this->resetValidation();
        $this->mode = $mode === 'pending' ? 'pending' : 'done';
        $this->date = $this->mode === 'pending' ? now()->addDay()->format('Y-m-d') : now()->format('Y-m-d');
        $this->time = $this->mode === 'pending' ? '' : now()->format('H:i');
        $this->showForm = true;
    }

    public function save(CallService $calls): void
    {
        Gate::authorize('update', $this->contact());

        $data = $this->validate([
            'mode' => ['required', 'in:done,pending'],
            'type' => ['required', Rule::enum(FollowupType::class)],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'outcome' => ['nullable', Rule::enum(CallOutcome::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'next_date' => ['nullable', 'date', 'after_or_equal:date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $outcome = $data['mode'] === 'done' && $data['outcome'] ? CallOutcome::from($data['outcome']) : null;

        $calls->log($this->contact(), [
            'type' => $data['type'],
            'date' => $data['date'],
            'time' => $data['time'] ?: null,
            'notes' => $data['notes'] ?: null,
            'outcome' => $outcome,
            'status' => $data['mode'] === 'pending'
                ? FollowupStatus::Pending
                : ($outcome && ! $outcome->reached() ? FollowupStatus::Missed : FollowupStatus::Done),
            'next_followup_date' => $data['mode'] === 'done' ? ($data['next_date'] ?: null) : null,
            'assigned_to' => $data['assigned_to'] ?: null,
        ]);

        $this->reset(['showForm', 'outcome', 'notes', 'next_date']);
        $this->lead?->refresh();
        $this->dispatch('calls-updated');
        $this->dispatch('toast', message: $data['mode'] === 'pending' ? 'Call scheduled.' : 'Call logged.', type: 'success');
    }

    public function startComplete(int $id): void
    {
        $this->resetValidation();
        $this->completing = $id;
        $this->complete_outcome = 'talked';
        $this->complete_result = '';
        $this->complete_next_date = '';
    }

    public function complete(CallService $calls): void
    {
        $call = $this->find($this->completing);
        abort_unless($calls->canManage(Auth::user(), $call), 403);

        $data = $this->validate([
            'complete_outcome' => ['required', Rule::enum(CallOutcome::class)],
            'complete_result' => ['nullable', 'string', 'max:2000'],
            'complete_next_date' => ['nullable', 'date'],
        ]);

        $calls->complete($call, CallOutcome::from($data['complete_outcome']), $data['complete_result'] ?: null, $data['complete_next_date'] ?: null);

        $this->completing = null;
        $this->dispatch('calls-updated');
        $this->dispatch('toast', message: $data['complete_next_date'] ? 'Call updated — next call scheduled.' : 'Call updated.', type: 'success');
    }

    public function deleteCall(int $id, CallService $calls): void
    {
        $call = $this->find($id);
        abort_unless($calls->canManage(Auth::user(), $call), 403);
        $call->delete();

        $this->dispatch('calls-updated');
        $this->dispatch('toast', message: 'Call removed.', type: 'success');
    }

    private function find(?int $id): LeadFollowup
    {
        return $this->query()->whereKey($id)->firstOrFail();
    }

    private function query()
    {
        return $this->lead
            ? LeadFollowup::query()->where('lead_id', $this->lead->id)
            : LeadFollowup::query()->where('client_id', $this->client->id);
    }

    public function render()
    {
        $all = $this->query()->with(['assignee:id,name', 'lead:id,lead_no'])
            ->orderByDesc('date')->orderByDesc('time')->orderByDesc('id')->get();
        $contact = $this->contact();

        return view('livewire.calls.panel', [
            'scheduled' => $all->where('status', FollowupStatus::Pending)->sortBy(fn ($c) => $c->date->format('Y-m-d').' '.($c->time ?? '99'))->values(),
            'history' => $all->where('status', '!=', FollowupStatus::Pending)->values(),
            'phone' => $contact->phone,
            'canEdit' => Gate::allows('update', $contact),
            'staff' => User::staff()->orderBy('name')->get(['id', 'name']),
            'today' => today(),
        ]);
    }
}
