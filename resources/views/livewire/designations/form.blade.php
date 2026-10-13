<div>
    <x-page-header :title='$designation ? "Edit {$designation->name}" : "New Designation"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Designations' => route('designations.index'), ($designation ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-lg space-y-6">
        <div class="panel grid grid-cols-1 gap-4 p-6">
            <x-select label="Department *" name="department_id" wire:model="department_id">
                <option value="">Select…</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </x-select>
            <x-input label="Name *" name="name" wire:model="name" />
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $designation ? 'Save changes' : 'Create designation' }}</x-button>
            <x-button variant="secondary" href="{{ route('designations.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
