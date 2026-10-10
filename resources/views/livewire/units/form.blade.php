<div>
    <x-breadcrumbs :items="['Units' => route('units.index'), ($unit ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $unit ? "Edit {$unit->name}" : 'New Unit' }}
    </h1>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="card grid grid-cols-1 gap-4 p-6">
            <div>
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" placeholder="e.g. Kilogram" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Short Code *</label>
                <input wire:model="short_code" type="text" placeholder="e.g. kg" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('short_code') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $unit ? 'Save changes' : 'Create unit' }}</button>
            <a href="{{ route('units.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
