<div>
    <x-page-header :title="$warranty ? 'Edit Warranty' : 'New Warranty'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warranties' => route('warranties.index'), ($warranty ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Project *" name="project_id" wire:model="project_id" @disabled($warranty) class="disabled:bg-ink-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <x-input label="Start Date *" name="start_date" type="date" wire:model="start_date" />
            <x-input label="End Date *" name="end_date" type="date" wire:model="end_date" />
            <div class="col-span-2">
                <x-textarea label="Terms" name="terms" rows="3" wire:model="terms" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $warranty ? 'Save changes' : 'Create warranty' }}</x-button>
            <x-button variant="secondary" href="{{ $warranty ? route('warranties.show', $warranty) : route('warranties.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
