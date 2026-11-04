<?php

namespace App\Livewire\Projects;

use App\Enums\ProjectStatus;
use App\Models\Expense;
use App\Models\Payment;
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

    /** Columns the list may be sorted by (the value comes from the URL). */
    private const SORTABLE = ['created_at', 'project_code', 'name', 'status', 'progress', 'budget', 'paid', 'balance'];

    public function render()
    {
        $showMoney = (bool) auth()->user()?->can('view_finance');

        $filtered = Project::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('project_code', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->projectManager, fn ($q) => $q->where('project_manager_id', $this->projectManager));

        $sort = in_array($this->sort, self::SORTABLE, true) ? $this->sort : 'created_at';
        if (! $showMoney && in_array($sort, ['budget', 'paid', 'balance'], true)) {
            $sort = 'created_at';
        }
        $direction = $this->direction === 'asc' ? 'asc' : 'desc';

        // Budget = the contract value, or the estimated budget until a
        // contract is signed. Paid = payments received for the project.
        // Balance = paid minus the project's expenses (cash in hand).
        $projects = (clone $filtered)
            ->with(['client', 'projectManager'])
            ->when($showMoney, fn ($q) => $q
                ->withSum('payments as paid_sum', 'amount')
                ->withSum('expenses as expense_sum', 'amount')
                ->selectRaw('COALESCE(projects.contract_value, projects.estimated_budget, 0) as budget_value'))
            ->when(in_array($sort, ['budget', 'paid', 'balance'], true), fn ($q) => match ($sort) {
                'budget' => $q->orderBy('budget_value', $direction),
                'paid' => $q->orderBy('paid_sum', $direction),
                'balance' => $q->orderByRaw(
                    '(select coalesce(sum(amount), 0) from payments where payments.project_id = projects.id and payments.deleted_at is null)'
                    .' - (select coalesce(sum(amount), 0) from expenses where expenses.project_id = projects.id and expenses.deleted_at is null) '.$direction
                ),
            }, fn ($q) => $q->orderBy($sort, $direction))
            ->paginate(15);

        // Totals across every project matching the filters, not just
        // this page.
        $summary = null;
        if ($showMoney) {
            $ids = (clone $filtered)->select('projects.id');
            $paid = (float) Payment::whereIn('project_id', $ids)->sum('amount');
            $expenses = (float) Expense::whereIn('project_id', $ids)->sum('amount');
            $summary = [
                'count' => (clone $filtered)->count(),
                'budget' => (float) (clone $filtered)->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(contract_value, estimated_budget, 0)')),
                'paid' => $paid,
                'expenses' => $expenses,
                'balance' => $paid - $expenses,
            ];
        }

        return view('livewire.projects.index', [
            'projects' => $projects,
            'summary' => $summary,
            'showMoney' => $showMoney,
            'statuses' => ProjectStatus::options(),
            'managers' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
