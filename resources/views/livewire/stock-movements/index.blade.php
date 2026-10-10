<div>
    <x-breadcrumbs :items="['Stock Movements' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">Stock Movements</h1>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <label class="form-label">Product</label>
            <select wire:model.live="product" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any product</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Warehouse</label>
            <select wire:model.live="warehouse" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any warehouse</option>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Type</label>
            <select wire:model.live="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($movements->isEmpty())
            <x-empty-state
                title="No stock movements found"
                description="Movements appear here as opening stock, transfers and requisitions move inventory."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Warehouse</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($movements as $movement)
                        <tr wire:key="movement-{{ $movement->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M, Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                @if ($movement->product)
                                    <a href="{{ route('products.show', $movement->product) }}" wire:navigate class="hover:underline">{{ $movement->product->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->warehouse?->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $movement->type->label() }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ $movement->quantity >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($movement->quantity, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($movement->balance_after, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
