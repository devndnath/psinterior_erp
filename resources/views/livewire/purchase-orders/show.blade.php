<div>
    <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), $purchaseOrder->po_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $purchaseOrder->po_no }}</h1>
                <x-status-badge :label="$purchaseOrder->status->label()" :color="$purchaseOrder->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $purchaseOrder->vendor?->company_name }}
                @if ($purchaseOrder->project)
                    &middot; <a href="{{ route('projects.show', $purchaseOrder->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $purchaseOrder->project->project_code }}</a>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $purchaseOrder)
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('send', $purchaseOrder)
                @if ($purchaseOrder->status->value === 'draft')
                    <x-confirm-dialog
                        title="Send this purchase order?"
                        message="This marks the order as sent to the vendor."
                        action="send"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-primary">Send</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
            @if (in_array($purchaseOrder->status->value, ['sent', 'partially_received'], true))
                @can('create_goods_receives')
                    <a href="{{ route('goods-receives.create', ['purchase_order' => $purchaseOrder->id]) }}" wire:navigate class="btn-primary">Receive Goods</a>
                @endcan
            @endif
            @can('cancel', $purchaseOrder)
                @if (in_array($purchaseOrder->status->value, ['draft', 'sent'], true))
                    <x-confirm-dialog
                        title="Cancel this purchase order?"
                        message="This marks the order as cancelled. It can only be done before any goods have been received."
                        action="cancel"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-secondary text-red-600">Cancel</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
            @can('delete', $purchaseOrder)
                <x-confirm-dialog
                    title="Delete this purchase order?"
                    :message='"This will remove {$purchaseOrder->po_no} and its items. This cannot be undone."'
                    action="deletePurchaseOrder"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $item->product?->name }} <span class="text-gray-400">({{ $item->product?->sku }})</span></td>
                                <td class="px-3 py-2 text-right text-sm text-gray-500">{{ $item->qty }}</td>
                                <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                                <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($purchaseOrder->terms)
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Terms</h2>
                    <p class="whitespace-pre-line text-sm text-gray-900">{{ $purchaseOrder->terms }}</p>
                </div>
            @endif

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Goods Receives</h2>
                @if ($purchaseOrder->goodsReceives->isEmpty())
                    <p class="text-sm text-gray-500">No goods received against this order yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">GRN #</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Received Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Received By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($purchaseOrder->goodsReceives as $grn)
                                <tr>
                                    <td class="px-3 py-2 text-sm">
                                        <a href="{{ route('goods-receives.show', $grn) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $grn->grn_no }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $grn->received_date?->format('d M, Y') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $grn->receivedBy?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Totals</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium text-gray-900">{{ number_format($purchaseOrder->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="font-medium text-gray-900">{{ number_format($purchaseOrder->discount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd class="font-medium text-gray-900">{{ number_format($purchaseOrder->tax, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="font-medium text-gray-700">Total</dt><dd class="text-lg font-semibold text-gray-900">{{ number_format($purchaseOrder->total, 2) }}</dd></div>
                </dl>
                <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm">
                    <div><dt class="text-gray-500">Receiving Warehouse</dt><dd class="font-medium text-gray-900">{{ $purchaseOrder->warehouse?->name }}</dd></div>
                    <div><dt class="text-gray-500">Date</dt><dd class="font-medium text-gray-900">{{ $purchaseOrder->date?->format('d M, Y') }}</dd></div>
                    @if ($purchaseOrder->expected_delivery)
                        <div><dt class="text-gray-500">Expected Delivery</dt><dd class="font-medium text-gray-900">{{ $purchaseOrder->expected_delivery->format('d M, Y') }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
