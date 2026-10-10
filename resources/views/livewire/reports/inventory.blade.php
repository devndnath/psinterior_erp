<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Inventory' => null]" class="mb-4" />

    <div class="mb-4 flex justify-end">
        <button type="button" wire:click="exportCsv" class="btn-secondary">Export CSV</button>
    </div>

    <div class="mb-6 card overflow-hidden overflow-x-auto">
        <div class="border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Stock on hand</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Warehouse</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">SKU</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Quantity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($stockByWarehouse as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row->warehouse?->name }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->product?->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row->product?->sku }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $row->quantity }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No stock on hand.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card overflow-hidden overflow-x-auto">
        <div class="border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Low stock</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total Stock</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Min Stock</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($lowStock as $product)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $product->category?->name }}</td>
                        <td class="px-4 py-3 text-right text-sm text-red-600">{{ $product->total_stock ?? 0 }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $product->min_stock }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No low-stock products.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
