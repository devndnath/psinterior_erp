<?php

namespace App\Livewire\MaterialRequisitions;

use App\Models\MaterialRequisition;
use App\Models\Warehouse;
use App\Services\MaterialRequisitionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public MaterialRequisition $materialRequisition;

    public string $issue_warehouse_id = '';

    public function mount(MaterialRequisition $materialRequisition): void
    {
        Gate::authorize('view', $materialRequisition);

        $this->materialRequisition = $materialRequisition;
        $this->issue_warehouse_id = (string) $materialRequisition->warehouse_id;
    }

    public function approveByManager(MaterialRequisitionService $service): void
    {
        Gate::authorize('approveByManager', $this->materialRequisition);

        $this->materialRequisition = $service->approveByManager($this->materialRequisition);

        $this->dispatch('toast', message: "Requisition {$this->materialRequisition->mr_no} approved by manager.", type: 'success');
    }

    public function approveByStore(MaterialRequisitionService $service): void
    {
        Gate::authorize('approveByStore', $this->materialRequisition);

        $this->materialRequisition = $service->approveByStore($this->materialRequisition);

        $this->dispatch('toast', message: "Requisition {$this->materialRequisition->mr_no} approved by store.", type: 'success');
    }

    public function reject(MaterialRequisitionService $service): void
    {
        Gate::authorize('reject', $this->materialRequisition);

        $this->materialRequisition = $service->reject($this->materialRequisition);

        $this->dispatch('toast', message: "Requisition {$this->materialRequisition->mr_no} rejected.", type: 'success');
    }

    public function issue(MaterialRequisitionService $service): void
    {
        Gate::authorize('issue', $this->materialRequisition);

        if (! $this->materialRequisition->warehouse_id && ! $this->issue_warehouse_id) {
            $this->addError('issue_warehouse_id', 'Select a warehouse to issue stock from.');

            return;
        }

        try {
            $this->materialRequisition = $service->issue(
                $this->materialRequisition,
                $this->issue_warehouse_id ? (int) $this->issue_warehouse_id : null,
                Auth::id(),
            );

            $this->dispatch('toast', message: "Requisition {$this->materialRequisition->mr_no} issued.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function deleteRequisition(): void
    {
        Gate::authorize('delete', $this->materialRequisition);

        $no = $this->materialRequisition->mr_no;
        $this->materialRequisition->delete();

        $this->dispatch('toast', message: "Material requisition {$no} deleted.", type: 'success');
        $this->redirectRoute('material-requisitions.index', navigate: true);
    }

    #[Title('Material Requisition')]
    public function render()
    {
        $this->materialRequisition->load(['project', 'warehouse', 'requestedBy', 'items.product']);

        return view('livewire.material-requisitions.show', [
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
