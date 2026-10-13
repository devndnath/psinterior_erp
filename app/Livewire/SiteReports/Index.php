<?php

namespace App\Livewire\SiteReports;

use App\Models\Project;
use App\Models\SiteReport;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Site Reports'])]
#[Title('Site Reports')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $project = '';

    #[Url]
    public string $sort = 'report_date';

    #[Url]
    public string $direction = 'desc';

    public function mount(): void
    {
        Gate::authorize('viewAny', SiteReport::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'project'], true)) {
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
        $this->reset(['search', 'project']);
    }

    public function deleteSiteReport(int $siteReportId): void
    {
        $report = SiteReport::findOrFail($siteReportId);
        Gate::authorize('delete', $report);

        $report->delete();

        $this->dispatch('toast', message: 'Site report deleted.', type: 'success');
    }

    public function render()
    {
        $reports = SiteReport::query()
            ->with(['project', 'reporter'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('work_summary', 'like', "%{$this->search}%")
                ->orWhereHas('project', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->orderBy($this->sort, $this->direction)
            ->paginate(15);

        return view('livewire.site-reports.index', [
            'reports' => $reports,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
