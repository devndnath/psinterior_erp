<div>
    <x-page-header title="Stock Movements" description="A read-only ledger of every stock movement — opening stock, transfers, and requisitions.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Stock Movements' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Product" name="product" wire:model.live="product">
                <option value="">Any product</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Warehouse" name="warehouse" wire:model.live="warehouse">
                <option value="">Any warehouse</option>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($movements->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No stock movements found"
                description="Movements appear here as opening stock, transfers and requisitions move inventory."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Warehouse</th>
                    <th>Type</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movements as $movement)
                    <tr wire:key="movement-{{ $movement->id }}">
                        <td class="text-ink-500">{{ $movement->created_at->format('d M, Y H:i') }}</td>
                        <td class="text-ink-900">
                            @if ($movement->product)
                                <a href="{{ route('products.show', $movement->product) }}" wire:navigate class="hover:underline">{{ $movement->product->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-ink-500">{{ $movement->warehouse?->name }}</td>
                        <td class="text-ink-500">{{ $movement->type->label() }}</td>
                        <td class="text-right font-medium {{ $movement->quantity >= 0 ? 'text-success-700' : 'text-danger-600' }}">{{ number_format($movement->quantity, 2) }}</td>
                        <td class="text-right text-ink-900">{{ number_format($movement->balance_after, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $movements->links() }}
        </div>
    @endif
</div>
