<div>
    <x-page-header :title="$purchaseOrder->po_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Orders' => route('purchase-orders.index'), $purchaseOrder->po_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$purchaseOrder->status->label()" :color="$purchaseOrder->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            @can('update', $purchaseOrder)
                <x-button variant="secondary" href="{{ route('purchase-orders.edit', $purchaseOrder) }}" wire:navigate>Edit</x-button>
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
                    <x-button href="{{ route('goods-receives.create', ['purchase_order' => $purchaseOrder->id]) }}" wire:navigate>Receive Goods</x-button>
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
                            <button type="button" class="btn-secondary text-danger-600">Cancel</button>
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
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $purchaseOrder->vendor?->company_name }}
        @if ($purchaseOrder->project)
            &middot; <a href="{{ route('projects.show', $purchaseOrder->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $purchaseOrder->project->project_code }}</a>
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Line items</h2>
                <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            <tr>
                                <td class="text-ink-900">{{ $item->product?->name }} <span class="text-ink-400">({{ $item->product?->sku }})</span></td>
                                <td class="text-right">{{ $item->qty }}</td>
                                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            @if ($purchaseOrder->terms)
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Terms</h2>
                    <p class="whitespace-pre-line text-sm text-ink-900">{{ $purchaseOrder->terms }}</p>
                </div>
            @endif

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Goods Receives</h2>
                @if ($purchaseOrder->goodsReceives->isEmpty())
                    <p class="text-sm text-ink-500">No goods received against this order yet.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>GRN #</th>
                                <th>Received Date</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseOrder->goodsReceives as $grn)
                                <tr>
                                    <td>
                                        <a href="{{ route('goods-receives.show', $grn) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $grn->grn_no }}</a>
                                    </td>
                                    <td>{{ $grn->received_date?->format('d M, Y') }}</td>
                                    <td>{{ $grn->receivedBy?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Totals</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-medium text-ink-900">{{ number_format($purchaseOrder->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Discount</dt><dd class="font-medium text-ink-900">{{ number_format($purchaseOrder->discount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Tax</dt><dd class="font-medium text-ink-900">{{ number_format($purchaseOrder->tax, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-medium text-ink-700">Total</dt><dd class="text-lg font-semibold text-ink-900">{{ number_format($purchaseOrder->total, 2) }}</dd></div>
                </dl>
                <dl class="mt-4 space-y-2 border-t border-ink-100 pt-4 text-sm">
                    <div><dt class="text-ink-500">Receiving Warehouse</dt><dd class="font-medium text-ink-900">{{ $purchaseOrder->warehouse?->name }}</dd></div>
                    <div><dt class="text-ink-500">Date</dt><dd class="font-medium text-ink-900">{{ $purchaseOrder->date?->format('d M, Y') }}</dd></div>
                    @if ($purchaseOrder->expected_delivery)
                        <div><dt class="text-ink-500">Expected Delivery</dt><dd class="font-medium text-ink-900">{{ $purchaseOrder->expected_delivery->format('d M, Y') }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
