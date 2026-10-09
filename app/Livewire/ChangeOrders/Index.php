<?php

namespace App\Livewire\ChangeOrders;

use App\Enums\ChangeOrderStatus;
use App\Models\ChangeOrder;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Change Orders'])]
#[Title('Change Orders')]
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
        Gate::authorize('viewAny', ChangeOrder::class);
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

    public function deleteChangeOrder(int $changeOrderId): void
    {
        $changeOrder = ChangeOrder::findOrFail($changeOrderId);
        Gate::authorize('delete', $changeOrder);

        $changeOrder->delete();

        $this->dispatch('toast', message: "Change order {$changeOrder->co_no} deleted.", type: 'success');
    }

    public function render()
    {
        $changeOrders = ChangeOrder::query()
            ->with('project')
            ->when($this->search, fn ($q) => $q->where('co_no', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.change-orders.index', [
            'changeOrders' => $changeOrders,
            'statuses' => ChangeOrderStatus::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
