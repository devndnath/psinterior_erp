<?php

namespace App\Livewire\Snags;

use App\Enums\SnagStatus;
use App\Models\Project;
use App\Models\Snag;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Snags'])]
#[Title('Snags')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $project = '';

    #[Url]
    public string $assigned_to = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Snag::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['status', 'project', 'assigned_to'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'project', 'assigned_to']);
    }

    public function deleteSnag(int $snagId): void
    {
        $snag = Snag::findOrFail($snagId);
        Gate::authorize('delete', $snag);

        $snag->delete();

        $this->dispatch('toast', message: "Snag {$snag->snag_no} deleted.", type: 'success');
    }

    public function render()
    {
        $snags = Snag::query()
            ->with(['project', 'assignedTo'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->when($this->assigned_to, fn ($q) => $q->where('assigned_to', $this->assigned_to))
            ->latest()
            ->paginate(15);

        return view('livewire.snags.index', [
            'snags' => $snags,
            'statuses' => SnagStatus::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'users' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
