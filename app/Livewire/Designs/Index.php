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

    /** '' = all, 'project' = project designs, 'sold' = designs sold without a project. */
    #[Url]
    public string $type = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Design::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status', 'category', 'project', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'category', 'project', 'type']);
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
            ->with(['project.client', 'client', 'revisions'])
            // What has been billed / paid for each design (Design Sale
            // invoices linked to it; cancelled ones don't count).
            ->withSum(['invoices as billed_sum' => fn ($q) => $q->where('status', '!=', \App\Enums\InvoiceStatus::Cancelled->value)], 'grand_total')
            ->withSum(['invoices as paid_sum' => fn ($q) => $q->where('status', '!=', \App\Enums\InvoiceStatus::Cancelled->value)], 'paid_amount')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('design_no', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->when($this->type === 'sold', fn ($q) => $q->whereNull('project_id'))
            ->when($this->type === 'project', fn ($q) => $q->whereNotNull('project_id'))
            ->latest()
            ->paginate(15);

        // Design sales at a glance (Design Sale invoices — usually sold
        // without a project — and what's been received against them),
        // shown to users who can see invoices.
        $summary = [
            'designs' => Design::count(),
            'inReview' => Design::whereIn('status', [DesignStatus::Submitted->value, DesignStatus::UnderReview->value])->count(),
            'approved' => Design::where('status', DesignStatus::Approved->value)->count(),
            'sold' => Design::whereNull('project_id')->count(),
            'sales' => null,
        ];
        if (auth()->user()?->can('view_invoices')) {
            $sales = \App\Models\Invoice::where('type', \App\Enums\InvoiceType::Design->value)
                ->where('status', '!=', \App\Enums\InvoiceStatus::Cancelled->value);
            $invoiced = (float) (clone $sales)->sum('grand_total');
            $received = (float) (clone $sales)->sum('paid_amount');
            $summary['sales'] = [
                'invoiced' => $invoiced,
                'received' => $received,
                'due' => max(0, $invoiced - $received),
                'count' => (clone $sales)->count(),
            ];
        }

        return view('livewire.designs.index', [
            'designs' => $designs,
            'summary' => $summary,
            'statuses' => DesignStatus::options(),
            'categories' => DesignCategory::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
