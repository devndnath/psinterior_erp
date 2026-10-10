<div>
    <x-breadcrumbs :items="['Products' => route('products.index'), $product->sku => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
                @if ($product->isLowStock())
                    <x-status-badge label="Low Stock" color="red" />
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $product->sku }} — {{ $product->category?->name }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $product)
                <a href="{{ route('products.edit', $product) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $product)
                <x-confirm-dialog
                    title="Delete this product?"
                    :message='"This will remove {$product->name}. This cannot be undone."'
                    action="deleteProduct"
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
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Brand</dt><dd class="font-medium text-gray-900">{{ $product->brand ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Unit</dt><dd class="font-medium text-gray-900">{{ $product->unit?->name }} ({{ $product->unit?->short_code }})</dd></div>
                    <div><dt class="text-gray-500">Purchase Price</dt><dd class="font-medium text-gray-900">{{ number_format($product->purchase_price, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Usage Rate</dt><dd class="font-medium text-gray-900">{{ $product->usage_rate !== null ? number_format($product->usage_rate, 2) : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Min Stock</dt><dd class="font-medium text-gray-900">{{ number_format($product->min_stock, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Total Stock</dt><dd class="font-medium {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($totalStock, 2) }}</dd></div>
                </dl>
                @if ($product->description)
                    <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $product->description }}</p>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Stock by Warehouse</h2>
                @if ($stockPerWarehouse->isEmpty())
                    <p class="text-sm text-gray-500">No stock recorded for this product yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Warehouse</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($stockPerWarehouse as $stock)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        <a href="{{ route('warehouses.show', $stock->warehouse) }}" wire:navigate class="hover:underline">{{ $stock->warehouse?->name }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($stock->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Recent Stock Movements</h2>
                @if ($recentMovements->isEmpty())
                    <p class="text-sm text-gray-500">No stock movements recorded yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Warehouse</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentMovements as $movement)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $movement->created_at->format('d M, Y H:i') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $movement->warehouse?->name }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $movement->type->label() }}</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium {{ $movement->quantity >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($movement->quantity, 2) }}</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-900">{{ number_format($movement->balance_after, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Stock</h2>
                <p class="text-3xl font-semibold {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($totalStock, 2) }}</p>
                <p class="mt-1 text-xs text-gray-400">Total across all warehouses. Reorder threshold: {{ number_format($product->min_stock, 2) }}.</p>
            </div>
        </div>
    </div>
</div>
