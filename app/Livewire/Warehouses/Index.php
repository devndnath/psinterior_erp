<?php

namespace App\Livewire\Warehouses;

use App\Enums\WarehouseType;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Warehouses'])]
#[Title('Warehouses')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('viewAny', Warehouse::class);
    }

    public function deleteWarehouse(int $warehouseId): void
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        Gate::authorize('delete', $warehouse);

        $warehouse->delete();

        $this->dispatch('toast', message: "Warehouse \"{$warehouse->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.warehouses.index', [
            'warehouses' => Warehouse::orderBy('name')->paginate(15),
            'types' => WarehouseType::options(),
        ]);
    }
}
