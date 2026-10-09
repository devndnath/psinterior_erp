<?php

namespace App\Livewire\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Quotations'])]
#[Title('Quotations')]
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
        Gate::authorize('viewAny', Quotation::class);
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

    public function deleteQuotation(int $quotationId): void
    {
        $quotation = Quotation::findOrFail($quotationId);
        Gate::authorize('delete', $quotation);

        $quotation->delete();

        $this->dispatch('toast', message: "Quotation {$quotation->quotation_no} deleted.", type: 'success');
    }

    public function render()
    {
        $quotations = Quotation::query()
            ->with(['project', 'client'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('quotation_no', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.quotations.index', [
            'quotations' => $quotations,
            'statuses' => QuotationStatus::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
