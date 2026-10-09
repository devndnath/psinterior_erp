<?php

namespace App\Livewire\Boqs;

use App\Enums\BoqStatus;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'BOQs'])]
#[Title('BOQs')]
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
        Gate::authorize('viewAny', Boq::class);
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

    public function deleteBoq(int $boqId): void
    {
        $boq = Boq::findOrFail($boqId);
        Gate::authorize('delete', $boq);

        $boq->delete();

        $this->dispatch('toast', message: "BOQ {$boq->boq_no} deleted.", type: 'success');
    }

    public function render()
    {
        $boqs = Boq::query()
            ->with('project')
            ->when($this->search, fn ($q) => $q->where('boq_no', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.boqs.index', [
            'boqs' => $boqs,
            'statuses' => BoqStatus::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
