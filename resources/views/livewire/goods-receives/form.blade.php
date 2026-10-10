<div>
    <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), $purchaseOrder->po_no => route('purchase-orders.show', $purchaseOrder), 'Receive Goods' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">Receive Goods — {{ $purchaseOrder->po_no }}</h1>

    <form wire:submit="save" class="max-w-5xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">Received Date *</label>
                <input wire:model="received_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('received_date') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Items</h2>

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                        <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Ordered</th>
                        <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Already Received</th>
                        <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Received Qty</th>
                        <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Damaged Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $index => $item)
                        <tr wire:key="grn-item-{{ $index }}">
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $item['product_name'] }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item['ordered_qty'], 2) }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item['already_received'], 2) }}</td>
                            <td class="px-3 py-2 text-right">
                                <input wire:model="items.{{ $index }}.received_qty" type="number" step="0.01" class="block w-28 rounded-lg border-gray-300 text-right shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                @error("items.{$index}.received_qty") <p class="form-error">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-3 py-2 text-right">
                                <input wire:model="items.{{ $index }}.damaged_qty" type="number" step="0.01" class="block w-28 rounded-lg border-gray-300 text-right shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                @error("items.{$index}.damaged_qty") <p class="form-error">{{ $message }}</p> @enderror
                            </td>
                        </tr>
                        @if (isset($this->overReceiptWarnings[$index]))
                            <tr>
                                <td colspan="5" class="px-3 pb-2 text-xs text-yellow-600">{{ $this->overReceiptWarnings[$index] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3 text-xs text-gray-400">Accepted quantity (received − damaged) is what reaches inventory. Damaged quantity cannot exceed received quantity.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Record Goods Receive</button>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
