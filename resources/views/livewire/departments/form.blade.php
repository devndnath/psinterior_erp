<div>
    <x-page-header :title='$department ? "Edit {$department->name}" : "New Department"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Departments' => route('departments.index'), ($department ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="panel grid grid-cols-1 gap-4 p-6">
            <x-input label="Name *" name="name" wire:model="name" />
            <x-input label="Code" name="code" wire:model="code" placeholder="e.g. OPS" />
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $department ? 'Save changes' : 'Create department' }}</x-button>
            <x-button variant="secondary" href="{{ route('departments.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
