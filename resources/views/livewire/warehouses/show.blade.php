<div>
    <x-breadcrumbs :items="['Warehouses' => route('warehouses.index'), $warehouse->name => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $warehouse->name }}</h1>
                <x-status-badge :label="$warehouse->type->label()" color="blue" />
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ $warehouse->location ?: 'No location set' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $warehouse)
                <a href="{{ route('warehouses.edit', $warehouse) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $warehouse)
                <x-confirm-dialog
                    title="Delete this warehouse?"
                    :message='"This will remove {$warehouse->name}. This cannot be undone."'
                    action="deleteWarehouse"
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
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Stock by Product</h2>
                @if ($stockPerProduct->isEmpty())
                    <p class="text-sm text-gray-500">No stock recorded in this warehouse yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($stockPerProduct as $stock)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        <a href="{{ route('products.show', $stock->product) }}" wire:navigate class="hover:underline">{{ $stock->product?->name }}</a>
                                        <span class="text-gray-400">({{ $stock->product?->sku }})</span>
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($stock->quantity, 2) }}</td>
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
                    <div class="card p-6">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Stock Entry</h2>
                        <div class="flex gap-2">
                            <button type="button" wire:click="toggleStockForm('opening')" class="btn-secondary flex-1 text-sm">
                                {{ $showStockForm && $stock_mode === 'opening' ? 'Cancel' : 'Opening Stock' }}
                            </button>
                            <button type="button" wire:click="toggleStockForm('adjust')" class="btn-secondary flex-1 text-sm">
                                {{ $showStockForm && $stock_mode === 'adjust' ? 'Cancel' : 'Adjust Stock' }}
                            </button>
                        </div>

                        @if ($showStockForm)
                            <form wire:submit="recordStockEntry" class="mt-4 space-y-3">
                                <div>
                                    <label class="form-label">Product *</label>
                                    <select wire:model="stock_product_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                        <option value="">Select…</option>
                                        @foreach ($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('stock_product_id') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="form-label">Quantity *</label>
                                    <input wire:model="stock_quantity" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                    @error('stock_quantity') <p class="form-error">{{ $message }}</p> @enderror
                                    @if ($stock_mode === 'adjust')
                                        <p class="mt-1 text-xs text-gray-400">A negative value decreases stock; this is the one entry allowed to go below zero.</p>
                                    @endif
                                </div>
                                <button type="submit" class="btn-primary w-full">Save</button>
                            </form>
                        @endif
                    </div>
                @endcan
            @endcan
        </div>
    </div>
</div>
