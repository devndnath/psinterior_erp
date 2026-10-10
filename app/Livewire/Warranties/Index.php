<?php

namespace App\Livewire\Warranties;

use App\Models\Project;
use App\Models\Warranty;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Warranties'])]
#[Title('Warranties')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $project = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Warranty::class);
    }

    public function updating($property): void
    {
        if ($property === 'project') {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['project']);
    }

    public function deleteWarranty(int $warrantyId): void
    {
        $warranty = Warranty::findOrFail($warrantyId);
        Gate::authorize('delete', $warranty);

        $warranty->delete();

        $this->dispatch('toast', message: 'Warranty deleted.', type: 'success');
    }

    public function render()
    {
        $warranties = Warranty::query()
            ->with('project')
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest('end_date')
            ->paginate(15);

        return view('livewire.warranties.index', [
            'warranties' => $warranties,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
