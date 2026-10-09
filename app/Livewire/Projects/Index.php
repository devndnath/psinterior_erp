<?php

namespace App\Livewire\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Projects'])]
#[Title('Projects')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $projectManager = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $direction = 'desc';

    public function mount(): void
    {
        Gate::authorize('viewAny', Project::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'projectManager'], true)) {
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
        $this->reset(['search', 'status', 'projectManager']);
    }

    public function deleteProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);
        Gate::authorize('delete', $project);

        $project->delete();

        $this->dispatch('toast', message: "Project {$project->project_code} deleted.", type: 'success');
    }

    public function render()
    {
        $projects = Project::query()
            ->with(['client', 'projectManager'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('project_code', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->projectManager, fn ($q) => $q->where('project_manager_id', $this->projectManager))
            ->orderBy($this->sort, $this->direction)
            ->paginate(15);

        return view('livewire.projects.index', [
            'projects' => $projects,
            'statuses' => ProjectStatus::options(),
            'managers' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
