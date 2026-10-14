<?php

namespace App\Livewire\Projects;

use App\Enums\LeadPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Project $project = null;

    public string $client_id = '';
    public ?int $lead_id = null;
    public string $name = '';
    public string $type = '';
    public string $location = '';
    public string $start_date = '';
    public string $expected_end_date = '';
    public string $contract_value = '';
    public string $estimated_budget = '';
    public string $project_manager_id = '';
    public string $designer_id = '';
    public string $site_engineer_id = '';
    public string $supervisor_id = '';
    public string $priority = 'medium';
    public string $status = 'planning';
    public string $description = '';

    public function mount(?Project $project = null): void
    {
        $this->project = $project?->exists ? $project : null;

        Gate::authorize($this->project ? 'update' : 'create', $this->project ?? Project::class);

        if ($this->project) {
            $this->fill([
                'client_id' => (string) $this->project->client_id,
                'lead_id' => $this->project->lead_id,
                'name' => $this->project->name,
                'type' => $this->project->type?->value ?? '',
                // `location` is nullable on `projects` but typed `string`
                // here — fill() throws a TypeError assigning null to a
                // non-nullable typed property (same bug class hit in
                // production on Clients\Form; see its mount() docblock).
                'location' => $this->project->location ?? '',
                'start_date' => $this->project->start_date?->format('Y-m-d') ?? '',
                'expected_end_date' => $this->project->expected_end_date?->format('Y-m-d') ?? '',
                'contract_value' => (string) $this->project->contract_value,
                'estimated_budget' => (string) $this->project->estimated_budget,
                'project_manager_id' => (string) $this->project->project_manager_id,
                'designer_id' => (string) $this->project->designer_id,
                'site_engineer_id' => (string) $this->project->site_engineer_id,
                'supervisor_id' => (string) $this->project->supervisor_id,
                'priority' => $this->project->priority->value,
                'status' => $this->project->status->value,
                'description' => (string) $this->project->description,
            ]);

            return;
        }

        // Pre-fill from a "Create Project" link on a Client or a converted
        // Lead (?client=&lead=) — the lead is never user-editable, only
        // carried through so ProjectService can stamp its conversion trail.
        $clientId = request()->query('client');
        $leadId = request()->query('lead');

        if ($clientId && Client::whereKey($clientId)->exists()) {
            $this->client_id = (string) $clientId;
        }

        if ($leadId && Lead::whereKey($leadId)->where('converted_client_id', $this->client_id ?: null)->exists()) {
            $this->lead_id = (int) $leadId;
        }
    }

    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:'.implode(',', array_column(ProjectType::cases(), 'value'))],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'designer_id' => ['nullable', 'exists:users,id'],
            'site_engineer_id' => ['nullable', 'exists:users,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string', 'in:'.implode(',', array_column(LeadPriority::cases(), 'value'))],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(ProjectStatus::cases(), 'value'))],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function save(ProjectService $service): void
    {
        $data = $this->validate();

        foreach (['project_manager_id', 'designer_id', 'site_engineer_id', 'supervisor_id', 'type'] as $nullable) {
            $data[$nullable] = $data[$nullable] !== '' ? $data[$nullable] : null;
        }

        if ($this->project) {
            $project = $service->update($this->project, $data);
            $this->dispatch('toast', message: 'Project updated.', type: 'success');
        } else {
            $data['lead_id'] = $this->lead_id;
            $project = $service->create($data);
            $this->dispatch('toast', message: "Project {$project->project_code} created.", type: 'success');
        }

        $this->redirectRoute('projects.show', ['project' => $project->id], navigate: true);
    }

    #[Title('Project')]
    public function render()
    {
        return view('livewire.projects.form', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'client_no']),
            'users' => User::staff()->orderBy('name')->get(['id', 'name']),
            'types' => ProjectType::options(),
            'priorities' => LeadPriority::options(),
            'statuses' => ProjectStatus::options(),
        ]);
    }
}
