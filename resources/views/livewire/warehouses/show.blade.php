<div>
    <x-page-header :title="$warehouse->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warehouses' => route('warehouses.index'), $warehouse->name => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$warehouse->type->label()" color="blue" />
            @can('update', $warehouse)
                <x-button variant="secondary" href="{{ route('warehouses.edit', $warehouse) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $warehouse)
                <x-confirm-dialog
                    title="Delete this warehouse?"
                    :message='"This will remove {$warehouse->name}. This cannot be undone."'
                    action="deleteWarehouse"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $warehouse->location ?: 'No location set' }}</p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Stock by Product</h2>
                @if ($stockPerProduct->isEmpty())
                    <p class="text-sm text-ink-500">No stock recorded in this warehouse yet.</p>
                @else
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockPerProduct as $stock)
                                <tr>
                                    <td class="text-ink-900">
                                        <a href="{{ route('products.show', $stock->product) }}" wire:navigate class="hover:underline">{{ $stock->product?->name }}</a>
                                        <span class="text-ink-400">({{ $stock->product?->sku }})</span>
                                    </td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($stock->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            @can('update', $warehouse)
                @can('manage_inventory')
                    <div class="panel p-6">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Stock Entry</h2>
                        <div class="flex gap-2">
                            <x-button variant="secondary" wire:click="toggleStockForm('opening')" class="flex-1 text-sm">
                                {{ $showStockForm && $stock_mode === 'opening' ? 'Cancel' : 'Opening Stock' }}
                            </x-button>
                            <x-button variant="secondary" wire:click="toggleStockForm('adjust')" class="flex-1 text-sm">
                                {{ $showStockForm && $stock_mode === 'adjust' ? 'Cancel' : 'Adjust Stock' }}
                            </x-button>
                        </div>

                        @if ($showStockForm)
                            <form wire:submit="recordStockEntry" class="mt-4 space-y-3">
                                <x-select label="Product *" name="stock_product_id" wire:model="stock_product_id">
                                    <option value="">Select…</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                                    @endforeach
                                </x-select>
                                <div>
                                    <x-input label="Quantity *" name="stock_quantity" type="number" step="0.01" wire:model="stock_quantity" />
                                    @if ($stock_mode === 'adjust')
                                        <p class="mt-1 text-xs text-ink-400">A negative value decreases stock; this is the one entry allowed to go below zero.</p>
                                    @endif
                                </div>
                                <x-button type="submit" class="w-full">Save</x-button>
                            </form>
                        @endif
                    </div>
                @endcan
            @endcan
        </div>
    </div>
</div>
