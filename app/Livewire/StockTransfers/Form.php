<?php

namespace App\Livewire\StockTransfers;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\StockTransferService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public string $from_warehouse_id = '';
    public string $to_warehouse_id = '';
    public string $notes = '';

    /** @var array<int, array{product_id:string,quantity:string}> */
    public array $items = [];

    public function mount(): void
    {
        Gate::authorize('create', StockTransfer::class);

        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => '', 'quantity' => '1'];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    protected function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function save(StockTransferService $service): void
    {
        $data = $this->validate();

        $transfer = $service->create($data, $data['items']);

        $this->dispatch('toast', message: "Stock transfer {$transfer->transfer_no} created.", type: 'success');
        $this->redirectRoute('stock-transfers.show', ['stockTransfer' => $transfer->id], navigate: true);
    }

    #[Title('New Stock Transfer')]
    public function render()
    {
        return view('livewire.stock-transfers.form', [
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
