<?php

namespace App\Livewire\Leads;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lead list — search, filter, sort, pagination (spec §48). Filter state
 * lives in the query string (#[Url]) so a filtered/sorted view is
 * shareable and survives a page refresh, without round-tripping through
 * the server for anything but the data itself.
 */
#[Layout('layouts.admin', ['title' => 'Leads'])]
#[Title('Leads')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public string $assignedTo = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $direction = 'desc';

    public function mount(): void
    {
        Gate::authorize('viewAny', Lead::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'priority', 'assignedTo'], true)) {
            $this->resetPage();
        }
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'priority', 'assignedTo']);
    }

    public function deleteLead(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        Gate::authorize('delete', $lead);

        $lead->delete();

        $this->dispatch('toast', message: "Lead {$lead->lead_no} deleted.", type: 'success');
    }

    public function render()
    {
        $leads = Lead::query()
            ->with(['source', 'assignee'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('lead_no', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->when($this->assignedTo, fn ($q) => $q->where('assigned_to', $this->assignedTo))
            ->orderBy($this->sort, $this->direction)
            ->paginate(15);

        return view('livewire.leads.index', [
            'leads' => $leads,
            'statuses' => LeadStatus::options(),
            'priorities' => LeadPriority::options(),
            'assignees' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
