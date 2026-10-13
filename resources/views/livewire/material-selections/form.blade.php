<div>
    <x-page-header :title="$board ? 'Edit Selection' : 'New Selection'">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Material Selections' => route('material-selections.index'), ($board ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Project *" name="project_id" wire:model="project_id" @disabled($board) class="disabled:bg-ink-50">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-span-2">
                <x-input label="Area / Room" name="area" wire:model="area" placeholder="e.g. Master Bath, Kitchen" />
            </div>
            <div class="col-span-2">
                <x-input label="Title *" name="title" wire:model="title" placeholder="e.g. Floor Tile" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Description" name="description" rows="3" wire:model="description" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $board ? 'Save changes' : 'Create selection' }}</x-button>
            <x-button variant="secondary" href="{{ $board ? route('material-selections.show', $board) : route('material-selections.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
