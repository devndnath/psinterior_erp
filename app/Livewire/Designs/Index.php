<?php

namespace App\Livewire\Designs;

use App\Enums\DesignCategory;
use App\Enums\DesignStatus;
use App\Models\Design;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Designs'])]
#[Title('Designs')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Design::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'category', 'project'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'category', 'project']);
    }

    public function deleteDesign(int $designId): void
    {
        $design = Design::findOrFail($designId);
        Gate::authorize('delete', $design);

        $design->delete();

        $this->dispatch('toast', message: "Design {$design->design_no} deleted.", type: 'success');
    }

    public function render()
    {
        $designs = Design::query()
            ->with(['project.client', 'revisions'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('design_no', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.designs.index', [
            'designs' => $designs,
            'statuses' => DesignStatus::options(),
            'categories' => DesignCategory::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
