<div>
    <x-page-header :title='$product ? "Edit {$product->name}" : "New Product"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Products' => route('products.index'), ($product ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-input label="SKU *" name="sku" wire:model="sku" />
            <x-input label="Name *" name="name" wire:model="name" />
            <x-select label="Category *" name="category_id" wire:model="category_id">
                <option value="">Select…</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </x-select>
            <x-select label="Unit *" name="unit_id" wire:model="unit_id">
                <option value="">Select…</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_code }})</option>
                @endforeach
            </x-select>
            <x-input label="Brand" name="brand" wire:model="brand" />
            <x-input label="Purchase Price *" name="purchase_price" type="number" step="0.01" wire:model="purchase_price" />
            <x-input label="Usage Rate" name="usage_rate" type="number" step="0.01" placeholder="Typical consumption rate" wire:model="usage_rate" />
            <x-input label="Min Stock (reorder level) *" name="min_stock" type="number" step="0.01" wire:model="min_stock" />
            <div class="col-span-2">
                <label class="form-label">Image</label>
                <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-ink-600">
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
                @if ($product?->image)
                    <p class="mt-1 text-xs text-ink-400">Current image on file. Choose a new one to replace it.</p>
                @endif
            </div>
            <div class="col-span-2">
                <x-textarea label="Description" name="description" rows="3" wire:model="description" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $product ? 'Save changes' : 'Create product' }}</x-button>
            <x-button variant="secondary" href="{{ $product ? route('products.show', $product) : route('products.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
