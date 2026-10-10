<?php

namespace App\Livewire\GoodsReceives;

use App\Models\GoodsReceive;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiveService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Always reached from a PO's Show page (`?purchase_order={id}`), the
 * same context-only access pattern as VendorQuotations/Form — there is
 * no separate Index.
 */
#[Layout('layouts.admin')]
class Form extends Component
{
    public PurchaseOrder $purchaseOrder;

    public string $received_date = '';
    public string $notes = '';

    /** @var array<int, array{purchase_order_item_id:int,product_name:string,ordered_qty:float,already_received:float,received_qty:string,damaged_qty:string}> */
    public array $items = [];

    public function mount(): void
    {
        Gate::authorize('create', GoodsReceive::class);

        $this->purchaseOrder = PurchaseOrder::with('items.product')->findOrFail(request()->query('purchase_order'));

        $this->received_date = now()->toDateString();

        $this->items = $this->purchaseOrder->items->map(fn ($item) => [
            'purchase_order_item_id' => $item->id,
            'product_name' => $item->product?->name.' ('.$item->product?->sku.')',
            'ordered_qty' => (float) $item->qty,
            'already_received' => $item->totalAcceptedQty(),
            'received_qty' => '0',
            'damaged_qty' => '0',
        ])->all();
    }

    protected function rules(): array
    {
        return [
            'received_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
            'items.*.damaged_qty' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Client-side validation beyond the hard damaged<=received rule
     * enforced by the service: a soft warning (not a hard block) when
     * this receipt would push the cumulative total well past the
     * ordered quantity — over-receiving can legitimately happen (e.g.
     * a vendor ships a bit extra), so it's surfaced, not refused.
     */
    public function getOverReceiptWarningsProperty(): array
    {
        $warnings = [];

        foreach ($this->items as $index => $item) {
            $received = (float) ($item['received_qty'] ?: 0);
            $projected = $item['already_received'] + $received;

            if ($received > 0 && $item['ordered_qty'] > 0 && $projected > $item['ordered_qty'] * 1.1) {
                $warnings[$index] = "This would bring total received to {$projected}, above the ordered {$item['ordered_qty']}.";
            }
        }

        return $warnings;
    }

    public function save(GoodsReceiveService $service): void
    {
        $data = $this->validate();

        foreach ($data['items'] as $index => $item) {
            if ((float) $item['damaged_qty'] > (float) $item['received_qty']) {
                $this->addError("items.{$index}.damaged_qty", 'Damaged quantity cannot exceed received quantity.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $items = array_map(fn ($item) => [
            'purchase_order_item_id' => $item['purchase_order_item_id'],
            'received_qty' => (float) $item['received_qty'],
            'damaged_qty' => (float) $item['damaged_qty'],
        ], $this->items);

        try {
            $grn = $service->create($this->purchaseOrder, [
                'received_date' => $data['received_date'],
                'received_by' => Auth::id(),
                'notes' => $data['notes'] ?: null,
            ], $items);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: "Goods receive {$grn->grn_no} recorded.", type: 'success');
        $this->redirectRoute('goods-receives.show', ['goodsReceive' => $grn->id], navigate: true);
    }

    #[Title('Receive Goods')]
    public function render()
    {
        return view('livewire.goods-receives.form');
    }
}
