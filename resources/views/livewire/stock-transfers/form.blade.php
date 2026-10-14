<div>
    <x-page-header title="New Stock Transfer">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Stock Transfers' => route('stock-transfers.index'), 'New' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-select label="From Warehouse *" name="from_warehouse_id" wire:model="from_warehouse_id">
                <option value="">Select…</option>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </x-select>
            <x-select label="To Warehouse *" name="to_warehouse_id" wire:model="to_warehouse_id">
                <option value="">Select…</option>
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </x-select>
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="2" wire:model="notes" />
            </div>
        </div>

        {{-- Repeating item-row fieldset (transferred items) stays
             hand-rolled/reskinned per the established precedent from
             Purchase Orders/BOQs/Invoices. --}}
        <div class="panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-ink-700">Items</h2>
                <button type="button" wire:click="addItem" class="text-sm text-accent-600 hover:underline">+ Add item</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="st-item-{{ $index }}" class="grid grid-cols-1 md:grid-cols-12 gap-2 rounded-lg border border-ink-100 p-3">
                        <div class="col-span-8">
                            <select wire:model="items.{{ $index }}.product_id" class="field-input">
                                <option value="">Select product…</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error("items.{$index}.product_id") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-3">
                            <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" placeholder="Qty" class="field-input">
                            @error("items.{$index}.quantity") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-1 flex items-center justify-end">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-sm text-danger-600 hover:text-danger-700">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Create transfer</x-button>
            <x-button variant="secondary" href="{{ route('stock-transfers.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
