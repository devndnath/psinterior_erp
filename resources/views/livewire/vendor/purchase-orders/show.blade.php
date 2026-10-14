<div>
    <x-page-header :title="$purchaseOrder->po_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Orders' => route('vendor.purchase-orders.index'), $purchaseOrder->po_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$purchaseOrder->status->label()" :color="$purchaseOrder->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="panel overflow-hidden">
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
                        <td class="font-medium text-ink-900">{{ $item->product?->name }}</td>
                        <td class="text-right">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="border-t border-ink-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between border-t border-ink-100 pt-1 text-base"><dt class="font-semibold text-ink-700">Total</dt><dd class="font-semibold text-ink-900">{{ number_format($purchaseOrder->total, 2) }}</dd></div>
            </dl>
        </div>
    </div>

    @if ($purchaseOrder->goodsReceives->isNotEmpty())
        <div class="mt-6 panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Goods Receive History</h2>
            <ul class="space-y-2">
                @foreach ($purchaseOrder->goodsReceives as $grn)
                    <li class="flex items-center justify-between rounded-lg border border-ink-100 p-3 text-sm">
                        <span class="font-medium text-ink-900">{{ $grn->grn_no }}</span>
                        <span class="text-ink-500">{{ $grn->received_date?->format('d M, Y') }} &middot; {{ $grn->items->count() }} item(s)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
