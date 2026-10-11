<div>
    <x-page-header title="Products" description="Track catalog items, categories and stock levels across warehouses.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Products' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <a href="{{ route('stock-movements.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-700">Stock Ledger</a>
            @can('manage_settings')
                <a href="{{ route('product-categories.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-700">Manage Categories</a>
                <a href="{{ route('units.index') }}" wire:navigate class="text-sm text-ink-500 hover:text-ink-700">Manage Units</a>
            @endcan
            @can('create', \App\Models\Product::class)
                <x-button href="{{ route('products.create') }}" wire:navigate>+ New Product</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="SKU or name…" />
        </div>
        <div>
            <x-select label="Category" name="category" wire:model.live="category">
                <option value="">Any category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </x-select>
        </div>
        <label class="flex items-center gap-2 pb-2">
            <input wire:model.live="lowStock" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
            <span class="text-sm text-ink-700">Low Stock only</span>
        </label>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($products->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No products found"
                description="Create a product to start tracking its stock."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th class="text-right">Stock</th>
                    <th class="text-right">Min Stock</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr wire:key="product-{{ $product->id }}">
                        <td class="font-mono">
                            <a href="{{ route('products.show', $product) }}" wire:navigate class="text-accent-600 hover:underline">{{ $product->sku }}</a>
                        </td>
                        <td class="text-ink-900">{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td class="text-right font-medium {{ (float) ($product->total_stock ?? 0) < (float) $product->min_stock ? 'text-danger-600' : 'text-ink-900' }}">
                            {{ number_format((float) ($product->total_stock ?? 0), 2) }}
                        </td>
                        <td class="text-right">{{ number_format($product->min_stock, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('products.show', $product) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $product)
                                <a href="{{ route('products.edit', $product) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
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
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>
