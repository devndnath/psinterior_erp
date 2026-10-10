<?php

namespace App\Livewire\Meetings;

use App\Enums\MeetingType;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use App\Services\MeetingService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Meeting $meeting = null;

    public string $project_id = '';
    public string $type = '';
    public string $date = '';
    public string $time = '';
    public string $location = '';
    public string $agenda = '';
    public string $discussion = '';
    public string $decisions = '';
    public string $follow_up = '';

    /** @var array<int,string> */
    public array $participant_users = [];

    /** @var array<int,string> */
    public array $participant_clients = [];

    /** @var array<int,string> */
    public array $participant_vendors = [];

    public function mount(?Meeting $meeting = null): void
    {
        $this->meeting = $meeting?->exists ? $meeting : null;

        Gate::authorize($this->meeting ? 'update' : 'create', $this->meeting ?? Meeting::class);

        if ($this->meeting) {
            $this->fill([
                'project_id' => (string) $this->meeting->project_id,
                'type' => $this->meeting->type->value,
                'date' => $this->meeting->date->toDateString(),
                'time' => (string) $this->meeting->time,
                'location' => (string) $this->meeting->location,
                'agenda' => (string) $this->meeting->agenda,
                'discussion' => (string) $this->meeting->discussion,
                'decisions' => (string) $this->meeting->decisions,
                'follow_up' => (string) $this->meeting->follow_up,
            ]);

            $this->meeting->load('participants');

            $this->participant_users = $this->meeting->participants
                ->where('participant_type', User::class)->pluck('participant_id')->map('strval')->all();
            $this->participant_clients = $this->meeting->participants
                ->where('participant_type', Client::class)->pluck('participant_id')->map('strval')->all();
            $this->participant_vendors = $this->meeting->participants
                ->where('participant_type', Vendor::class)->pluck('participant_id')->map('strval')->all();
        } else {
            $projectId = request()->query('project');

            if ($projectId && Project::whereKey($projectId)->exists()) {
                $this->project_id = (string) $projectId;
            }

            $this->date = now()->toDateString();
        }
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['nullable', 'exists:projects,id'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(MeetingType::cases(), 'value'))],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string', 'max:5000'],
            'discussion' => ['nullable', 'string', 'max:5000'],
            'decisions' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save(MeetingService $service): void
    {
        $data = $this->validate();
        $data['project_id'] = $data['project_id'] ?: null;

        $participantRefs = [
            ...array_map(fn ($id) => ['type' => 'user', 'id' => (int) $id], $this->participant_users),
            ...array_map(fn ($id) => ['type' => 'client', 'id' => (int) $id], $this->participant_clients),
            ...array_map(fn ($id) => ['type' => 'vendor', 'id' => (int) $id], $this->participant_vendors),
        ];

        if ($this->meeting) {
            $meeting = $service->update($this->meeting, $data, $participantRefs);
            $message = 'Meeting updated.';
        } else {
            $meeting = $service->create($data, $participantRefs);
            $message = 'Meeting created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('meetings.show', ['meeting' => $meeting->id], navigate: true);
    }

    #[Title('Meeting')]
    public function render()
    {
        return view('livewire.meetings.form', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'types' => MeetingType::options(),
            'users' => User::staff()->orderBy('name')->get(['id', 'name']),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'vendors' => Vendor::orderBy('company_name')->get(['id', 'company_name']),
        ]);
    }
}
