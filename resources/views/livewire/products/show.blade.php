<div>
    <x-page-header :title="$product->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Products' => route('products.index'), $product->sku => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($product->isLowStock())
                <x-status-badge label="Low Stock" color="red" />
            @endif
            @can('update', $product)
                <x-button variant="secondary" href="{{ route('products.edit', $product) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $product)
                <x-confirm-dialog
                    title="Delete this product?"
                    :message='"This will remove {$product->name}. This cannot be undone."'
                    action="deleteProduct"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $product->sku }} — {{ $product->category?->name }}
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Brand</dt><dd class="font-medium text-ink-900">{{ $product->brand ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Unit</dt><dd class="font-medium text-ink-900">{{ $product->unit?->name }} ({{ $product->unit?->short_code }})</dd></div>
                    <div><dt class="text-ink-500">Purchase Price</dt><dd class="font-medium text-ink-900">{{ number_format($product->purchase_price, 2) }}</dd></div>
                    <div><dt class="text-ink-500">Usage Rate</dt><dd class="font-medium text-ink-900">{{ $product->usage_rate !== null ? number_format($product->usage_rate, 2) : '—' }}</dd></div>
                    <div><dt class="text-ink-500">Min Stock</dt><dd class="font-medium text-ink-900">{{ number_format($product->min_stock, 2) }}</dd></div>
                    <div><dt class="text-ink-500">Total Stock</dt><dd class="font-medium {{ $product->isLowStock() ? 'text-danger-600' : 'text-ink-900' }}">{{ number_format($totalStock, 2) }}</dd></div>
                </dl>
                @if ($product->description)
                    <p class="mt-4 whitespace-pre-line text-sm text-ink-700">{{ $product->description }}</p>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Stock by Warehouse</h2>
                @if ($stockPerWarehouse->isEmpty())
                    <p class="text-sm text-ink-500">No stock recorded for this product yet.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th class="text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockPerWarehouse as $stock)
                                <tr>
                                    <td class="text-ink-900">
                                        <a href="{{ route('warehouses.show', $stock->warehouse) }}" wire:navigate class="hover:underline">{{ $stock->warehouse?->name }}</a>
                                    </td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($stock->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Recent Stock Movements</h2>
                @if ($recentMovements->isEmpty())
                    <p class="text-sm text-ink-500">No stock movements recorded yet.</p>
                @else
                    <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Warehouse</th>
                                <th>Type</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentMovements as $movement)
                                <tr>
                                    <td class="text-ink-500">{{ $movement->created_at->format('d M, Y H:i') }}</td>
                                    <td class="text-ink-900">{{ $movement->warehouse?->name }}</td>
                                    <td class="text-ink-500">{{ $movement->type->label() }}</td>
                                    <td class="text-right font-medium {{ $movement->quantity >= 0 ? 'text-success-700' : 'text-danger-600' }}">{{ number_format($movement->quantity, 2) }}</td>
                                    <td class="text-right text-ink-900">{{ number_format($movement->balance_after, 2) }}</td>
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
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Stock</h2>
                <p class="text-3xl font-semibold {{ $product->isLowStock() ? 'text-danger-600' : 'text-ink-900' }}">{{ number_format($totalStock, 2) }}</p>
                <p class="mt-1 text-xs text-ink-400">Total across all warehouses. Reorder threshold: {{ number_format($product->min_stock, 2) }}.</p>
            </div>
        </div>
    </div>
</div>
