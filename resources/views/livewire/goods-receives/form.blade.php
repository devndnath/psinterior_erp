<div>
    <x-page-header :title="'Receive Goods — '.$purchaseOrder->po_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), $purchaseOrder->po_no => route('purchase-orders.show', $purchaseOrder), 'Receive Goods' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-5xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <x-input label="Received Date *" name="received_date" type="date" wire:model="received_date" />
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        {{-- Repeating item-row fieldset (received-quantity rows) stays
             hand-rolled/reskinned per the established precedent from
             Purchase Orders/BOQs/Invoices. --}}
        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Items</h2>

            <table class="table-base">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Ordered</th>
                        <th class="text-right">Already Received</th>
                        <th class="text-right">Received Qty</th>
                        <th class="text-right">Damaged Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr wire:key="grn-item-{{ $index }}">
                            <td class="text-ink-900">{{ $item['product_name'] }}</td>
                            <td class="text-right text-ink-500">{{ number_format($item['ordered_qty'], 2) }}</td>
                            <td class="text-right text-ink-500">{{ number_format($item['already_received'], 2) }}</td>
                            <td class="text-right">
                                <input wire:model="items.{{ $index }}.received_qty" type="number" step="0.01" class="field-input w-28 text-right">
                                @error("items.{$index}.received_qty") <p class="form-error">{{ $message }}</p> @enderror
                            </td>
                            <td class="text-right">
                                <input wire:model="items.{{ $index }}.damaged_qty" type="number" step="0.01" class="field-input w-28 text-right">
                                @error("items.{$index}.damaged_qty") <p class="form-error">{{ $message }}</p> @enderror
                            </td>
                        </tr>
                        @if (isset($this->overReceiptWarnings[$index]))
                            <tr>
                                <td colspan="5" class="px-4 pb-2 text-xs text-warning-600">{{ $this->overReceiptWarnings[$index] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3 text-xs text-ink-400">Accepted quantity (received − damaged) is what reaches inventory. Damaged quantity cannot exceed received quantity.</p>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Record Goods Receive</x-button>
            <x-button variant="secondary" href="{{ route('purchase-orders.show', $purchaseOrder) }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
