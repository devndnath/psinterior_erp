<div>
    <x-page-header :title='$productCategory ? "Edit {$productCategory->name}" : "New Product Category"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Product Categories' => route('product-categories.index'), ($productCategory ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="panel grid grid-cols-1 gap-4 p-6">
            <x-input label="Name *" name="name" wire:model="name" />
            <label class="flex items-center gap-2">
                <input wire:model="is_active" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
                <span class="text-sm text-ink-700">Active</span>
            </label>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $productCategory ? 'Save changes' : 'Create category' }}</x-button>
            <x-button variant="secondary" href="{{ route('product-categories.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
