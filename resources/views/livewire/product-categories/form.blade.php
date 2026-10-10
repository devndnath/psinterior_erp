<div>
    <x-breadcrumbs :items="['Product Categories' => route('product-categories.index'), ($productCategory ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $productCategory ? "Edit {$productCategory->name}" : 'New Product Category' }}
    </h1>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="card grid grid-cols-1 gap-4 p-6">
            <div>
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2">
                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500">
                <span class="text-sm text-gray-700">Active</span>
            </label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $productCategory ? 'Save changes' : 'Create category' }}</button>
            <a href="{{ route('product-categories.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
