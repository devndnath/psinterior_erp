<?php

namespace App\Livewire\Inspections;

use App\Enums\InspectionResult;
use App\Models\Inspection;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Inspections'])]
#[Title('Inspections')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $result = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Inspection::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['result', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['result', 'project']);
    }

    public function deleteInspection(int $inspectionId): void
    {
        $inspection = Inspection::findOrFail($inspectionId);
        Gate::authorize('delete', $inspection);

        $inspection->delete();

        $this->dispatch('toast', message: 'Inspection deleted.', type: 'success');
    }

    public function render()
    {
        $inspections = Inspection::query()
            ->with('project')
            ->when($this->result, fn ($q) => $q->where('result', $this->result))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest('inspection_date')
            ->paginate(15);

        return view('livewire.inspections.index', [
            'inspections' => $inspections,
            'results' => InspectionResult::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
