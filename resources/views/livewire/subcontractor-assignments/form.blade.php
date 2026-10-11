<div>
    <x-page-header title="New Subcontractor Assignment">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['New Assignment' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div>
                <x-select label="Subcontractor *" name="subcontractor_id" wire:model="subcontractor_id">
                    <option value="">Select…</option>
                    @foreach ($subcontractors as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-select label="Project *" name="project_id" wire:model="project_id">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-span-2">
                <x-textarea label="Work Description" name="work_description" rows="3" wire:model="work_description" />
            </div>
            <x-input label="Rate *" name="rate" type="number" step="0.01" wire:model="rate" />
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">Create assignment</x-button>
            <x-button variant="secondary" href="{{ url()->previous() }}">Cancel</x-button>
        </div>
    </form>
</div>
