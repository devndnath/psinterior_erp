<div>
    <x-breadcrumbs :items="['Products' => route('products.index'), ($product ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $product ? "Edit {$product->name}" : 'New Product' }}
    </h1>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div>
                <label class="form-label">SKU *</label>
                <input wire:model="sku" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('sku') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Category *</label>
                <select wire:model="category_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Unit *</label>
                <select wire:model="unit_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">Select…</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_code }})</option>
                    @endforeach
                </select>
                @error('unit_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Brand</label>
                <input wire:model="brand" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('brand') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Purchase Price *</label>
                <input wire:model="purchase_price" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('purchase_price') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Usage Rate</label>
                <input wire:model="usage_rate" type="number" step="0.01" placeholder="Typical consumption rate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('usage_rate') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Min Stock (reorder level) *</label>
                <input wire:model="min_stock" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('min_stock') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Image</label>
                <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-gray-600">
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
                @if ($product?->image)
                    <p class="mt-1 text-xs text-gray-400">Current image on file. Choose a new one to replace it.</p>
                @endif
            </div>
            <div class="col-span-2">
                <label class="form-label">Description</label>
                <textarea wire:model="description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $product ? 'Save changes' : 'Create product' }}</button>
            <a href="{{ $product ? route('products.show', $product) : route('products.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
