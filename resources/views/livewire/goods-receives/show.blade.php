<div>
    <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), $goodsReceive->purchaseOrder->po_no => route('purchase-orders.show', $goodsReceive->purchaseOrder), $goodsReceive->grn_no => null]" class="mb-4" />

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $goodsReceive->grn_no }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            Against <a href="{{ route('purchase-orders.show', $goodsReceive->purchaseOrder) }}" wire:navigate class="text-brand-600 hover:underline">{{ $goodsReceive->purchaseOrder->po_no }}</a>
            — {{ $goodsReceive->purchaseOrder->vendor?->company_name }}
        </p>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Received Items</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Ordered</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Received</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Damaged</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Accepted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($goodsReceive->items as $item)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $item->purchaseOrderItem?->product?->name }} <span class="text-gray-400">({{ $item->purchaseOrderItem?->product?->sku }})</span></td>
                                <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->ordered_qty, 2) }}</td>
                                <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->received_qty, 2) }}</td>
                                <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->damaged_qty, 2) }}</td>
                                <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->accepted_qty, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($goodsReceive->notes)
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Notes</h2>
                    <p class="whitespace-pre-line text-sm text-gray-900">{{ $goodsReceive->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Receipt Details</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-gray-500">Received Date</dt><dd class="font-medium text-gray-900">{{ $goodsReceive->received_date?->format('d M, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Received By</dt><dd class="font-medium text-gray-900">{{ $goodsReceive->receivedBy?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Warehouse</dt><dd class="font-medium text-gray-900">{{ $goodsReceive->purchaseOrder->warehouse?->name }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
