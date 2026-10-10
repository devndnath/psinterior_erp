<?php

namespace App\Livewire\StockTransfers;

use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public StockTransfer $stockTransfer;

    public function mount(StockTransfer $stockTransfer): void
    {
        Gate::authorize('view', $stockTransfer);

        $this->stockTransfer = $stockTransfer;
    }

    public function ship(StockTransferService $service): void
    {
        Gate::authorize('ship', $this->stockTransfer);

        $this->stockTransfer = $service->ship($this->stockTransfer);

        $this->dispatch('toast', message: "Stock transfer {$this->stockTransfer->transfer_no} marked in transit.", type: 'success');
    }

    public function complete(StockTransferService $service): void
    {
        Gate::authorize('complete', $this->stockTransfer);

        try {
            $this->stockTransfer = $service->complete($this->stockTransfer, Auth::id());
            $this->dispatch('toast', message: "Stock transfer {$this->stockTransfer->transfer_no} completed.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function cancel(StockTransferService $service): void
    {
        Gate::authorize('cancel', $this->stockTransfer);

        try {
            $this->stockTransfer = $service->cancel($this->stockTransfer);
            $this->dispatch('toast', message: "Stock transfer {$this->stockTransfer->transfer_no} cancelled.", type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    #[Title('Stock Transfer')]
    public function render()
    {
        $this->stockTransfer->load(['fromWarehouse', 'toWarehouse', 'items.product']);

        return view('livewire.stock-transfers.show');
    }
}
