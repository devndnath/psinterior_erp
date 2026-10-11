<div>
    <x-page-header :title="$goodsReceive->grn_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), $goodsReceive->purchaseOrder->po_no => route('purchase-orders.show', $goodsReceive->purchaseOrder), $goodsReceive->grn_no => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        Against <a href="{{ route('purchase-orders.show', $goodsReceive->purchaseOrder) }}" wire:navigate class="text-accent-600 hover:underline">{{ $goodsReceive->purchaseOrder->po_no }}</a>
        — {{ $goodsReceive->purchaseOrder->vendor?->company_name }}
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Received Items</h2>
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Ordered</th>
                            <th class="text-right">Received</th>
                            <th class="text-right">Damaged</th>
                            <th class="text-right">Accepted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($goodsReceive->items as $item)
                            <tr>
                                <td class="text-ink-900">{{ $item->purchaseOrderItem?->product?->name }} <span class="text-ink-400">({{ $item->purchaseOrderItem?->product?->sku }})</span></td>
                                <td class="text-right text-ink-500">{{ number_format($item->ordered_qty, 2) }}</td>
                                <td class="text-right text-ink-500">{{ number_format($item->received_qty, 2) }}</td>
                                <td class="text-right text-ink-500">{{ number_format($item->damaged_qty, 2) }}</td>
                                <td class="text-right font-medium text-ink-900">{{ number_format($item->accepted_qty, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($goodsReceive->notes)
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Notes</h2>
                    <p class="whitespace-pre-line text-sm text-ink-900">{{ $goodsReceive->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Receipt Details</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-ink-500">Received Date</dt><dd class="font-medium text-ink-900">{{ $goodsReceive->received_date?->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">Received By</dt><dd class="font-medium text-ink-900">{{ $goodsReceive->receivedBy?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Warehouse</dt><dd class="font-medium text-ink-900">{{ $goodsReceive->purchaseOrder->warehouse?->name }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
