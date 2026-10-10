<div>
    <x-breadcrumbs :items="['Purchase Orders' => route('vendor.purchase-orders.index'), $purchaseOrder->po_no => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $purchaseOrder->po_no }}</h1>
            <x-status-badge :label="$purchaseOrder->status->label()" :color="$purchaseOrder->status->color()" />
        </div>
        <button type="button" wire:click="downloadPdf" class="btn-secondary">Download PDF</button>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($purchaseOrder->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $item->qty }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-gray-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between border-t border-gray-100 pt-1 text-base"><dt class="font-semibold text-gray-700">Total</dt><dd class="font-semibold text-gray-900">{{ number_format($purchaseOrder->total, 2) }}</dd></div>
            </dl>
        </div>
    </div>

    @if ($purchaseOrder->goodsReceives->isNotEmpty())
        <div class="mt-6 card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Goods Receive History</h2>
            <ul class="space-y-2">
                @foreach ($purchaseOrder->goodsReceives as $grn)
                    <li class="flex items-center justify-between rounded-lg border border-gray-100 p-3 text-sm">
                        <span class="font-medium text-gray-900">{{ $grn->grn_no }}</span>
                        <span class="text-gray-500">{{ $grn->received_date?->format('d M, Y') }} &middot; {{ $grn->items->count() }} item(s)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
