<div>
    <x-breadcrumbs :items="['Products' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('stock-movements.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700">Stock Ledger</a>
            @can('manage_settings')
                <a href="{{ route('product-categories.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700">Manage Categories</a>
                <a href="{{ route('units.index') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700">Manage Units</a>
            @endcan
            @can('create', \App\Models\Product::class)
                <a href="{{ route('products.create') }}" wire:navigate class="btn-primary">+ New Product</a>
            @endcan
        </div>
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="SKU or name…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Category</label>
            <select wire:model.live="category" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 pb-2">
            <input wire:model.live="lowStock" type="checkbox" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
            <span class="text-sm text-gray-700">Low Stock only</span>
        </label>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($products->isEmpty())
            <x-empty-state
                title="No products found"
                description="Create a product to start tracking its stock."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">SKU</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Min Stock</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($products as $product)
                        <tr wire:key="product-{{ $product->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('products.show', $product) }}" wire:navigate class="text-brand-600 hover:underline">{{ $product->sku }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ (float) ($product->total_stock ?? 0) < (float) $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                {{ number_format((float) ($product->total_stock ?? 0), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($product->min_stock, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('products.show', $product) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $product)
                                    <a href="{{ route('products.edit', $product) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $product)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this product?"
                                            :message='"This will remove {$product->name}. This cannot be undone."'
                                            action="deleteProduct"
                                            :params="[$product->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
