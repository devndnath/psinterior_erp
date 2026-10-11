<div>
    <x-page-header :title="$stockTransfer->transfer_no" :description="($stockTransfer->fromWarehouse?->name) . ' → ' . ($stockTransfer->toWarehouse?->name)">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Stock Transfers' => route('stock-transfers.index'), $stockTransfer->transfer_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$stockTransfer->status->label()" :color="$stockTransfer->status->color()" />
            @can('ship', $stockTransfer)
                @if ($stockTransfer->status->value === 'pending')
                    <x-button variant="secondary" wire:click="ship">Ship</x-button>
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
                            <button type="button" class="btn-secondary text-danger-600">Cancel</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Items</h2>
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stockTransfer->items as $item)
                            <tr>
                                <td class="text-ink-900">{{ $item->product?->name }} <span class="text-ink-400">({{ $item->product?->sku }})</span></td>
                                <td class="text-right font-medium text-ink-900">{{ number_format($item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($stockTransfer->notes)
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Notes</h2>
                    <p class="whitespace-pre-line text-sm text-ink-900">{{ $stockTransfer->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Route</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-ink-500">From</dt><dd class="font-medium text-ink-900">{{ $stockTransfer->fromWarehouse?->name }}</dd></div>
                    <div><dt class="text-ink-500">To</dt><dd class="font-medium text-ink-900">{{ $stockTransfer->toWarehouse?->name }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
