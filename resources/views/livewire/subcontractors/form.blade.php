<div>
    @php($pageTitle = $subcontractor ? "Edit {$subcontractor->name}" : 'New Subcontractor')

    <x-page-header :title="$pageTitle">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Subcontractors' => route('subcontractors.index'), ($subcontractor ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-input label="Name *" name="name" wire:model="name" />
            </div>
            <div>
                <x-select label="Trade *" name="trade" wire:model="trade">
                    @foreach ($trades as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <x-input label="Phone" name="phone" wire:model="phone" />
            <div class="col-span-2">
                <x-textarea label="Address" name="address" rows="2" wire:model="address" />
            </div>
            <x-input label="Rate Type" name="rate_type" wire:model="rate_type" placeholder="e.g. per sqft, per day, lump sum" />
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $subcontractor ? 'Save changes' : 'Create subcontractor' }}</x-button>
            <x-button variant="secondary" href="{{ $subcontractor ? route('subcontractors.show', $subcontractor) : route('subcontractors.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
