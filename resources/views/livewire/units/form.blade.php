<div>
    <x-page-header :title='$unit ? "Edit {$unit->name}" : "New Unit"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Units' => route('units.index'), ($unit ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="panel grid grid-cols-1 gap-4 p-6">
            <x-input label="Name *" name="name" placeholder="e.g. Kilogram" wire:model="name" />
            <x-input label="Short Code *" name="short_code" placeholder="e.g. kg" wire:model="short_code" />
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $unit ? 'Save changes' : 'Create unit' }}</x-button>
            <x-button variant="secondary" href="{{ route('units.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
