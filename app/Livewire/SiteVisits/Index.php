<?php

namespace App\Livewire\SiteVisits;

use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Site Visits'])]
#[Title('Site Visits')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $assignedTo = '';

    #[Url]
    public string $sort = 'visit_date';

    #[Url]
    public string $direction = 'desc';

    public function mount(): void
    {
        Gate::authorize('viewAny', SiteVisit::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'assignedTo'], true)) {
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
        $this->reset(['search', 'assignedTo']);
    }

    public function deleteSiteVisit(int $siteVisitId): void
    {
        $visit = SiteVisit::findOrFail($siteVisitId);
        Gate::authorize('delete', $visit);

        $visit->delete();

        $this->dispatch('toast', message: "Site visit {$visit->sv_no} deleted.", type: 'success');
    }

    public function render()
    {
        $visits = SiteVisit::query()
            ->with(['client', 'assignee'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('sv_no', 'like', "%{$this->search}%")
                ->orWhere('site_address', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->assignedTo, fn ($q) => $q->where('assigned_to', $this->assignedTo))
            ->orderBy($this->sort, $this->direction)
            ->paginate(15);

        return view('livewire.site-visits.index', [
            'visits' => $visits,
            'assignees' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
