<div>
    {{-- Phase 16: see reports/hr.blade.php — this report had no heading
         of its own and relied on layouts.admin's old fallback <h1>. --}}
    <x-page-header title="Inventory Report">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Inventory' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-button variant="secondary" class="no-print" wire:click="exportCsv">Export CSV</x-button>
            <x-button variant="secondary" class="no-print" onclick="window.print()">Print</x-button>
        </x-slot:actions>
    </x-page-header>

    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-ink-500">Stock on hand</h2>
    <x-table class="mb-6">
        <thead>
            <tr>
                <th>Warehouse</th>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-right">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stockByWarehouse as $row)
                <tr>
                    <td class="text-ink-500">{{ $row->warehouse?->name }}</td>
                    <td class="font-medium text-ink-900">{{ $row->product?->name }}</td>
                    <td class="text-ink-500">{{ $row->product?->sku }}</td>
                    <td class="text-right text-ink-500">{{ $row->quantity }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-ink-400">No stock on hand.</td></tr>
            @endforelse
        </tbody>
    </x-table>

    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-ink-500">Low stock</h2>
    <x-table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th class="text-right">Total Stock</th>
                <th class="text-right">Min Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lowStock as $product)
                <tr>
                    <td class="font-medium text-ink-900">{{ $product->name }}</td>
                    <td class="text-ink-500">{{ $product->category?->name }}</td>
                    <td class="text-right text-danger-600">{{ $product->total_stock ?? 0 }}</td>
                    <td class="text-right text-ink-500">{{ $product->min_stock }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-ink-400">No low-stock products.</td></tr>
            @endforelse
        </tbody>
    </x-table>
</div>
