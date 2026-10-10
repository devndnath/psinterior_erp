<div>
    <x-breadcrumbs :items="['Stock Transfers' => route('stock-transfers.index'), $stockTransfer->transfer_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $stockTransfer->transfer_no }}</h1>
                <x-status-badge :label="$stockTransfer->status->label()" :color="$stockTransfer->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $stockTransfer->fromWarehouse?->name }} &rarr; {{ $stockTransfer->toWarehouse?->name }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('ship', $stockTransfer)
                @if ($stockTransfer->status->value === 'pending')
                    <button type="button" wire:click="ship" class="btn-secondary">Ship</button>
                @endif
            @endcan
            @can('complete', $stockTransfer)
                @if (in_array($stockTransfer->status->value, ['pending', 'in_transit'], true))
                    <x-confirm-dialog
                        title="Complete this transfer?"
                        :message='"This will move stock from {$stockTransfer->fromWarehouse->name} to {$stockTransfer->toWarehouse->name}. This cannot be undone."'
                        action="complete"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-primary">Complete</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
            @can('cancel', $stockTransfer)
                @if (in_array($stockTransfer->status->value, ['pending', 'in_transit'], true))
                    <x-confirm-dialog
                        title="Cancel this transfer?"
                        message="This marks the transfer as cancelled. It can only be done before it is completed."
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
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Items</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($stockTransfer->items as $item)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $item->product?->name }} <span class="text-gray-400">({{ $item->product?->sku }})</span></td>
                                <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($stockTransfer->notes)
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Notes</h2>
                    <p class="whitespace-pre-line text-sm text-gray-900">{{ $stockTransfer->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Route</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-gray-500">From</dt><dd class="font-medium text-gray-900">{{ $stockTransfer->fromWarehouse?->name }}</dd></div>
                    <div><dt class="text-gray-500">To</dt><dd class="font-medium text-gray-900">{{ $stockTransfer->toWarehouse?->name }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
