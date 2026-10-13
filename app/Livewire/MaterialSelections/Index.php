<?php

namespace App\Livewire\MaterialSelections;

use App\Enums\SelectionBoardStatus;
use App\Models\Project;
use App\Models\SelectionBoard;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Material Selections'])]
#[Title('Material Selections')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $project = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', SelectionBoard::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['project', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['project', 'status']);
    }

    public function render()
    {
        $boards = SelectionBoard::query()
            ->with('project')
            ->withCount('options')
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.material-selections.index', [
            'boards' => $boards,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
            'statuses' => SelectionBoardStatus::options(),
        ]);
    }
}
