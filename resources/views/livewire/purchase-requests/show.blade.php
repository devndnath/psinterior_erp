<div>
    <x-breadcrumbs :items="['Purchase Requests' => route('purchase-requests.index'), $purchaseRequest->pr_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $purchaseRequest->pr_no }}</h1>
                <x-status-badge :label="$purchaseRequest->status->label()" :color="$purchaseRequest->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($purchaseRequest->project)
                    <a href="{{ route('projects.show', $purchaseRequest->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $purchaseRequest->project->project_code }}</a>
                    — {{ $purchaseRequest->project->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $purchaseRequest)
                <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('sendForQuotation', $purchaseRequest)
                @if ($purchaseRequest->status->value === 'draft')
                    <x-confirm-dialog
                        title="Send for quotation?"
                        message="Vendors can start submitting quotations against this purchase request once it is sent."
                        action="sendForQuotation"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-primary">Send for Quotation</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
            @can('cancel', $purchaseRequest)
                @if (! in_array($purchaseRequest->status->value, ['po_created', 'cancelled'], true))
                    <x-confirm-dialog
                        title="Cancel this purchase request?"
                        message="This marks the request as cancelled. It cannot be undone."
                        action="cancel"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-secondary text-red-600">Cancel</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Requested Items</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($purchaseRequest->items as $item)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $item->product?->name }} <span class="text-gray-400">({{ $item->product?->sku }})</span></td>
                                <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Vendor Quotations</h2>
                    @can('create', \App\Models\VendorQuotation::class)
                        @if (! in_array($purchaseRequest->status->value, ['draft', 'cancelled'], true))
                            <a href="{{ route('vendor-quotations.create', ['purchase_request' => $purchaseRequest->id]) }}" wire:navigate class="text-sm text-brand-600 hover:underline">+ Record Quotation</a>
                        @endif
                    @endcan
                </div>

                @if ($purchaseRequest->vendorQuotations->isEmpty())
                    <p class="text-sm text-gray-500">No vendor quotations recorded yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Vendor</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($purchaseRequest->vendorQuotations as $vq)
                                <tr wire:key="vq-{{ $vq->id }}">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $vq->vendor?->company_name }}</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($vq->total_amount, 2) }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <x-status-badge :label="$vq->status->label()" :color="$vq->status->color()" />
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm">
                                        @can('selectWinner', $vq)
                                            @if ($vq->status->value === 'received')
                                                <x-confirm-dialog
                                                    title="Select this quotation as the winner?"
                                                    :message='"This rejects every other quotation on {$purchaseRequest->pr_no} and marks the request Vendor Selected."'
                                                    action="selectWinner"
                                                    :params="[$vq->id]"
                                                >
                                                    <x-slot:trigger>
                                                        <button type="button" class="text-brand-600 hover:underline">Select Winner</button>
                                                    </x-slot:trigger>
                                                </x-confirm-dialog>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @php
                    $winner = $purchaseRequest->vendorQuotations->firstWhere('status', \App\Enums\VendorQuotationStatus::Selected);
                @endphp

                @if ($winner)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        @can('create', \App\Models\PurchaseOrder::class)
                            <a href="{{ route('purchase-orders.create', ['vendor_quotation' => $winner->id]) }}" wire:navigate class="btn-primary">Create Purchase Order</a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
