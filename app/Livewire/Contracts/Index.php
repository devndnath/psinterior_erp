<?php

namespace App\Livewire\Contracts;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Contracts'])]
#[Title('Contracts')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Contract::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'project']);
    }

    public function deleteContract(int $contractId): void
    {
        $contract = Contract::findOrFail($contractId);
        Gate::authorize('delete', $contract);

        $contract->delete();

        $this->dispatch('toast', message: "Contract {$contract->contract_no} deleted.", type: 'success');
    }

    public function render()
    {
        $contracts = Contract::query()
            ->with(['project', 'client'])
            ->when($this->search, fn ($q) => $q->where('contract_no', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.contracts.index', [
            'contracts' => $contracts,
            'statuses' => ContractStatus::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
