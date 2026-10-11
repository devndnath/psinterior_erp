<div>
    <x-page-header :title='$warehouse ? "Edit {$warehouse->name}" : "New Warehouse"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warehouses' => route('warehouses.index'), ($warehouse ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="panel grid grid-cols-1 gap-4 p-6">
            <x-input label="Name *" name="name" wire:model="name" />
            <x-select label="Type *" name="type" wire:model="type">
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <x-input label="Location" name="location" wire:model="location" />
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $warehouse ? 'Save changes' : 'Create warehouse' }}</x-button>
            <x-button variant="secondary" href="{{ $warehouse ? route('warehouses.show', $warehouse) : route('warehouses.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
