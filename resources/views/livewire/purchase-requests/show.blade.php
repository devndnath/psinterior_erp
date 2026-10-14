<div>
    <x-page-header :title="$purchaseRequest->pr_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Requests' => route('purchase-requests.index'), $purchaseRequest->pr_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$purchaseRequest->status->label()" :color="$purchaseRequest->status->color()" />
            @can('update', $purchaseRequest)
                <x-button variant="secondary" href="{{ route('purchase-requests.edit', $purchaseRequest) }}" wire:navigate>Edit</x-button>
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
                            <button type="button" class="btn-secondary text-danger-600">Cancel</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($purchaseRequest->project)
            <a href="{{ route('projects.show', $purchaseRequest->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $purchaseRequest->project->project_code }}</a>
            — {{ $purchaseRequest->project->name }}
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Requested Items</h2>
                <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseRequest->items as $item)
                            <tr>
                                <td class="text-ink-900">{{ $item->product?->name }} <span class="text-ink-400">({{ $item->product?->sku }})</span></td>
                                <td class="text-right font-medium text-ink-900">{{ number_format($item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Vendor Quotations</h2>
                    @can('create', \App\Models\VendorQuotation::class)
                        @if (! in_array($purchaseRequest->status->value, ['draft', 'cancelled'], true))
                            <a href="{{ route('vendor-quotations.create', ['purchase_request' => $purchaseRequest->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ Record Quotation</a>
                        @endif
                    @endcan
                </div>

                @if ($purchaseRequest->vendorQuotations->isEmpty())
                    <p class="text-sm text-ink-500">No vendor quotations recorded yet.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Vendor</th>
                                <th class="text-right">Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseRequest->vendorQuotations as $vq)
                                <tr wire:key="vq-{{ $vq->id }}">
                                    <td class="text-ink-900">{{ $vq->vendor?->company_name }}</td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($vq->total_amount, 2) }}</td>
                                    <td>
                                        <x-status-badge :label="$vq->status->label()" :color="$vq->status->color()" />
                                    </td>
                                    <td class="text-right">
                                        @can('selectWinner', $vq)
                                            @if ($vq->status->value === 'received')
                                                <x-confirm-dialog
                                                    title="Select this quotation as the winner?"
                                                    :message='"This rejects every other quotation on {$purchaseRequest->pr_no} and marks the request Vendor Selected."'
                                                    action="selectWinner"
                                                    :params="[$vq->id]"
                                                >
                                                    <x-slot:trigger>
                                                        <button type="button" class="text-accent-600 hover:underline">Select Winner</button>
                                                    </x-slot:trigger>
                                                </x-confirm-dialog>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif

                @php
                    $winner = $purchaseRequest->vendorQuotations->firstWhere('status', \App\Enums\VendorQuotationStatus::Selected);
                @endphp

                @if ($winner)
                    <div class="mt-4 border-t border-ink-100 pt-4">
                        @can('create', \App\Models\PurchaseOrder::class)
                            <x-button href="{{ route('purchase-orders.create', ['vendor_quotation' => $winner->id]) }}" wire:navigate>Create Purchase Order</x-button>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
