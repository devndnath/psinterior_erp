<div>
    <x-breadcrumbs :items="['Subcontractors' => route('subcontractors.index'), ($subcontractor ? 'Edit' : 'New') => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">
        {{ $subcontractor ? "Edit {$subcontractor->name}" : 'New Subcontractor' }}
    </h1>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="card grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <label class="form-label">Name *</label>
                <input wire:model="name" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Trade *</label>
                <select wire:model="trade" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    @foreach ($trades as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('trade') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input wire:model="phone" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="form-label">Address</label>
                <textarea wire:model="address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                @error('address') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Rate Type</label>
                <input wire:model="rate_type" type="text" placeholder="e.g. per sqft, per day, lump sum" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                @error('rate_type') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $subcontractor ? 'Save changes' : 'Create subcontractor' }}</button>
            <a href="{{ $subcontractor ? route('subcontractors.show', $subcontractor) : route('subcontractors.index') }}" wire:navigate class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
