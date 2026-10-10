<?php

namespace App\Livewire\Subcontractors;

use App\Enums\SubcontractorTrade;
use App\Models\Subcontractor;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Subcontractors'])]
#[Title('Subcontractors')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $trade = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Subcontractor::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'trade'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'trade']);
    }

    public function deleteSubcontractor(int $subcontractorId): void
    {
        $subcontractor = Subcontractor::findOrFail($subcontractorId);
        Gate::authorize('delete', $subcontractor);

        $subcontractor->delete();

        $this->dispatch('toast', message: "Subcontractor \"{$subcontractor->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        $subcontractors = Subcontractor::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->trade, fn ($q) => $q->where('trade', $this->trade))
            ->latest()
            ->paginate(15);

        return view('livewire.subcontractors.index', [
            'subcontractors' => $subcontractors,
            'trades' => SubcontractorTrade::options(),
        ]);
    }
}
