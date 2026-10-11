<div>
    <x-page-header title="Record Handover">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="[$project->name => route('projects.show', $project), 'Record Handover' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-input label="Handover Date *" name="handover_date" type="date" wire:model="handover_date" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Notes" name="notes" rows="3" wire:model="notes" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Record Handover</x-button>
            <x-button variant="secondary" href="{{ route('projects.show', $project) }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
