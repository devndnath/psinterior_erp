<?php

namespace App\Livewire\StockTransfers;

use App\Enums\StockTransferStatus;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Stock Transfers'])]
#[Title('Stock Transfers')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', StockTransfer::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
    }

    public function render()
    {
        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse'])
            ->when($this->search, fn ($q) => $q->where('transfer_no', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.stock-transfers.index', [
            'transfers' => $transfers,
            'statuses' => StockTransferStatus::options(),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
