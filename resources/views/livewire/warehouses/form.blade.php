<div>
    <x-breadcrumbs :items="['Warehouses' => route('warehouses.index'), ($warehouse ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $warehouse ? "Edit {$warehouse->name}" : 'New Warehouse' }}
    </h1>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="card grid grid-cols-1 gap-4 p-6">
            <div>
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Type *</label>
                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Location</label>
                <input wire:model="location" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('location') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $warehouse ? 'Save changes' : 'Create warehouse' }}</button>
            <a href="{{ $warehouse ? route('warehouses.show', $warehouse) : route('warehouses.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
