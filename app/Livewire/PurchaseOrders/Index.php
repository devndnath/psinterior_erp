<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\PurchaseOrderStatus;
use App\Models\Project;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Purchase Orders'])]
#[Title('Purchase Orders')]
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
        Gate::authorize('viewAny', PurchaseOrder::class);
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

    public function deletePurchaseOrder(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::findOrFail($purchaseOrderId);
        Gate::authorize('delete', $po);

        $po->delete();

        $this->dispatch('toast', message: "Purchase order {$po->po_no} deleted.", type: 'success');
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['project', 'vendor'])
            ->when($this->search, fn ($q) => $q->where('po_no', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->project, fn ($q) => $q->where('project_id', $this->project))
            ->latest()
            ->paginate(15);

        return view('livewire.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'statuses' => PurchaseOrderStatus::options(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'project_code']),
        ]);
    }
}
