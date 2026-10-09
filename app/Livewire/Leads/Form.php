<?php

namespace App\Livewire\Leads;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\ProjectType;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Lead $lead = null;

    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $company_name = '';
    public string $address = '';
    public string $project_type = '';
    public string $estimated_budget = '';
    public string $interested_service = '';
    public string $lead_source_id = '';
    public string $assigned_to = '';
    public string $priority = 'medium';
    public string $status = 'new';
    public string $expected_closing_date = '';
    public string $notes = '';

    public function mount(?Lead $lead = null): void
    {
        $this->lead = $lead?->exists ? $lead : null;

        Gate::authorize($this->lead ? 'update' : 'create', $this->lead ?? Lead::class);

        if ($this->lead) {
            $this->fill([
                'name' => $this->lead->name,
                'phone' => $this->lead->phone,
                'email' => $this->lead->email,
                'company_name' => $this->lead->company_name,
                'address' => $this->lead->address,
                'project_type' => $this->lead->project_type?->value ?? '',
                'estimated_budget' => (string) $this->lead->estimated_budget,
                'interested_service' => $this->lead->interested_service,
                'lead_source_id' => (string) $this->lead->lead_source_id,
                'assigned_to' => (string) $this->lead->assigned_to,
                'priority' => $this->lead->priority->value,
                'status' => $this->lead->status->value,
                'expected_closing_date' => $this->lead->expected_closing_date?->format('Y-m-d') ?? '',
                'notes' => $this->lead->notes,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'project_type' => ['nullable', 'string', 'in:'.implode(',', array_column(ProjectType::cases(), 'value'))],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'interested_service' => ['nullable', 'string', 'max:255'],
            'lead_source_id' => ['nullable', 'exists:lead_sources,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string', 'in:'.implode(',', array_column(LeadPriority::cases(), 'value'))],
            'status' => ['required', 'string', 'in:'.implode(',', array_column(LeadStatus::cases(), 'value'))],
            'expected_closing_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(LeadService $service): void
    {
        $data = $this->validate();
        $data['estimated_budget'] = $data['estimated_budget'] !== '' ? $data['estimated_budget'] : null;
        $data['lead_source_id'] = $data['lead_source_id'] !== '' ? $data['lead_source_id'] : null;
        $data['assigned_to'] = $data['assigned_to'] !== '' ? $data['assigned_to'] : null;
        $data['project_type'] = $data['project_type'] !== '' ? $data['project_type'] : null;
        $data['expected_closing_date'] = $data['expected_closing_date'] !== '' ? $data['expected_closing_date'] : null;

        if ($this->lead) {
            $service->update($this->lead, $data);
            $this->dispatch('toast', message: 'Lead updated.', type: 'success');
        } else {
            $lead = $service->create($data);
            $this->dispatch('toast', message: "Lead {$lead->lead_no} created.", type: 'success');
        }

        $this->redirectRoute('leads.index', navigate: true);
    }

    #[Title('Lead')]
    public function render()
    {
        return view('livewire.leads.form', [
            'sources' => LeadSource::active()->orderBy('name')->get(),
            'assignees' => User::staff()->orderBy('name')->get(['id', 'name']),
            'projectTypes' => ProjectType::options(),
            'priorities' => LeadPriority::options(),
            'statuses' => LeadStatus::options(),
        ]);
    }
}
