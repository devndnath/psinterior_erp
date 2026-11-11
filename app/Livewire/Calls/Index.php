<?php

namespace App\Livewire\Calls;

use App\Enums\CallOutcome;
use App\Enums\FollowupStatus;
use App\Enums\FollowupType;
use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use App\Services\CallService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Calls — every call and WhatsApp follow-up, for leads and clients, in
 * one list (Meetings section). Calls to make now, upcoming and history;
 * one-tap call / WhatsApp; log, schedule and finish calls. Same rows as
 * the lead and client call cards and the CRM overview.
 */
#[Layout('layouts.admin', ['title' => 'Calls'])]
#[Title('Calls')]
class Index extends Component
{
    use WithPagination;

    /** todo = overdue + today (default), overdue, today, week (next 7 days), month (next 30), upcoming, history, all */
    #[Url]
    public string $when = 'todo';

    /** '' both, lead, client */
    #[Url]
    public string $with = '';

    /** '' anyone, me, or a user id */
    #[Url]
    public string $assigned = '';

    /** '' calls & WhatsApp, all = every follow-up type, or one FollowupType */
    #[Url]
    public string $type = '';

    #[Url]
    public string $search = '';

    /** Narrow to one lead or client (the "open in Calls" link on their page). */
    #[Url]
    public ?int $lead = null;

    #[Url]
    public ?int $client = null;

    // Log / schedule form.
    public bool $showForm = false;
    public string $mode = 'pending';
    public string $contact = ''; // "lead:12" / "client:5"
    public string $call_type = 'call';
    public string $date = '';
    public string $time = '';
    public string $outcome = '';
    public string $notes = '';
    public string $next_date = '';
    public string $assigned_to = '';

    // Finishing a call.
    public ?int $completing = null;
    public string $complete_outcome = 'talked';
    public string $complete_result = '';
    public string $complete_next_date = '';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user?->can('view_leads') || $user?->can('view_clients'), 403);

        if ($this->lead || $this->client) {
            $this->when = $this->when === 'todo' ? 'all' : $this->when;
        }
    }

    public function updating($property): void
    {
        if (in_array($property, ['when', 'with', 'assigned', 'type', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['with', 'assigned', 'type', 'search', 'lead', 'client']);
        $this->when = 'todo';
        $this->resetPage();
    }

    public function openForm(string $mode = 'pending'): void
    {
        $this->resetValidation();
        $this->mode = $mode === 'done' ? 'done' : 'pending';
        $this->date = $this->mode === 'pending' ? now()->addDay()->format('Y-m-d') : now()->format('Y-m-d');
        $this->time = $this->mode === 'pending' ? '' : now()->format('H:i');
        $this->assigned_to = (string) Auth::id();
        $this->contact = $this->lead ? 'lead:'.$this->lead : ($this->client ? 'client:'.$this->client : '');
        $this->showForm = true;
    }

    /** The lead or client a "lead:12" / "client:5" picker value names, if this user may log calls for it. */
    private function resolveContact(string $value): Lead|Client|null
    {
        [$kind, $id] = array_pad(explode(':', $value, 2), 2, null);
        $model = match ($kind) {
            'lead' => Lead::find($id),
            'client' => Client::find($id),
            default => null,
        };

        return $model && Gate::allows('update', $model) ? $model : null;
    }

    public function save(CallService $calls): void
    {
        $data = $this->validate([
            'contact' => ['required', 'string'],
            'mode' => ['required', 'in:done,pending'],
            'call_type' => ['required', Rule::enum(FollowupType::class)],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'outcome' => ['nullable', Rule::enum(CallOutcome::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'next_date' => ['nullable', 'date', 'after_or_equal:date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ], [], ['contact' => 'lead or client', 'call_type' => 'type']);

        $contact = $this->resolveContact($data['contact']);
        if (! $contact) {
            $this->addError('contact', 'Choose a lead or client you can log calls for.');

            return;
        }

        $outcome = $data['mode'] === 'done' && $data['outcome'] ? CallOutcome::from($data['outcome']) : null;

        $calls->log($contact, [
            'type' => $data['call_type'],
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
        $call = $this->visible()->whereKey($this->completing)->firstOrFail();
        abort_unless($calls->canManage(Auth::user(), $call), 403);

        $data = $this->validate([
            'complete_outcome' => ['required', Rule::enum(CallOutcome::class)],
            'complete_result' => ['nullable', 'string', 'max:2000'],
            'complete_next_date' => ['nullable', 'date'],
        ]);

        $calls->complete($call, CallOutcome::from($data['complete_outcome']), $data['complete_result'] ?: null, $data['complete_next_date'] ?: null);

        $this->completing = null;
        $this->dispatch('toast', message: $data['complete_next_date'] ? 'Call updated — next call scheduled.' : 'Call updated.', type: 'success');
    }

    public function deleteCall(int $id, CallService $calls): void
    {
        $call = $this->visible()->whereKey($id)->firstOrFail();
        abort_unless($calls->canManage(Auth::user(), $call), 403);
        $call->delete();

        $this->dispatch('toast', message: 'Call removed.', type: 'success');
    }

    private function visible()
    {
        return LeadFollowup::query()->visibleTo(Auth::user());
    }

    /** Calls & WhatsApp unless another type (or all) is picked, for the chosen people. */
    private function base()
    {
        return $this->visible()
            ->when($this->type === '', fn ($q) => $q->calls())
            ->when($this->type !== '' && $this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->when($this->with === 'lead', fn ($q) => $q->whereNull('client_id'))
            ->when($this->with === 'client', fn ($q) => $q->whereNotNull('client_id'))
            ->when($this->lead, fn ($q) => $q->where('lead_id', $this->lead))
            ->when($this->client, fn ($q) => $q->where('client_id', $this->client))
            ->when($this->assigned === 'me', fn ($q) => $q->where('assigned_to', Auth::id()))
            ->when(ctype_digit($this->assigned), fn ($q) => $q->where('assigned_to', (int) $this->assigned))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn ($w) => $w
                    ->whereHas('lead', fn ($l) => $l->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('lead_no', 'like', $term))
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('client_no', 'like', $term))
                    ->orWhere('notes', 'like', $term));
            });
    }

    public function render()
    {
        $today = CarbonImmutable::today();
        $d = $today->toDateString();
        $toMake = fn () => $this->base()->toMake();

        $counts = [
            'overdue' => $toMake()->whereDate('date', '<', $d)->count(),
            'today' => $toMake()->whereDate('date', $d)->count(),
            'week' => $toMake()->whereDate('date', '>', $d)->whereDate('date', '<=', $today->addDays(7)->toDateString())->count(),
            'history' => $this->base()->where('status', '!=', FollowupStatus::Pending->value)
                ->whereDate('date', '>=', $today->startOfMonth()->toDateString())->count(),
        ];

        $query = match ($this->when) {
            'overdue' => $toMake()->whereDate('date', '<', $d)->orderBy('date')->orderBy('time'),
            'today' => $toMake()->whereDate('date', $d)->orderBy('time'),
            'week' => $toMake()->whereDate('date', '>', $d)->whereDate('date', '<=', $today->addDays(7)->toDateString())->orderBy('date')->orderBy('time'),
            'month' => $toMake()->whereDate('date', '>', $d)->whereDate('date', '<=', $today->addDays(30)->toDateString())->orderBy('date')->orderBy('time'),
            'upcoming' => $toMake()->whereDate('date', '>', $d)->orderBy('date')->orderBy('time'),
            'history' => $this->base()->where('status', '!=', FollowupStatus::Pending->value)->orderByDesc('date')->orderByDesc('time'),
            'all' => $this->base()->orderByDesc('date')->orderByDesc('time'),
            default => $toMake()->whereDate('date', '<=', $d)->orderBy('date')->orderBy('time'),
        };

        $user = Auth::user();
        $open = array_map(fn (LeadStatus $s) => $s->value, array_filter(LeadStatus::cases(), fn (LeadStatus $s) => $s->isOpen()));

        return view('livewire.calls.index', [
            'calls' => $query->with([...LeadFollowup::CONTACT_RELATIONS, 'assignee:id,name'])->orderBy('id')->paginate(20),
            'counts' => $counts,
            'today' => $today,
            'staff' => User::staff()->orderBy('name')->get(['id', 'name']),
            'types' => FollowupType::options(),
            'outcomes' => CallOutcome::options(),
            'focus' => $this->lead ? Lead::find($this->lead, ['id', 'name', 'lead_no']) : ($this->client ? Client::find($this->client, ['id', 'name', 'client_no']) : null),
            // People this user can log calls for, for the form's picker.
            'pickLeads' => $this->showForm && $user->can('edit_leads')
                ? Lead::whereIn('status', $open)->whereNull('converted_client_id')->orderBy('name')->get(['id', 'name', 'lead_no', 'phone'])
                : collect(),
            'pickClients' => $this->showForm && $user->can('edit_clients')
                ? Client::orderBy('name')->get(['id', 'name', 'client_no', 'phone'])
                : collect(),
            'canLog' => $user->can('edit_leads') || $user->can('edit_clients'),
        ]);
    }
}
