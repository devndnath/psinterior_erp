<div>
    @php($pageTitle = $snag ? "Edit Snag {$snag->snag_no}" : 'New Snag')
    <x-page-header :title="$pageTitle">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Snags' => route('snags.index'), ($snag ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2">
                <x-select label="Project *" name="project_id" wire:model="project_id" :disabled="(bool) $snag">
                    <option value="">Select…</option>
                    @foreach ($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                    @endforeach
                </x-select>
            </div>
            @if ($inspection)
                <div class="col-span-2 rounded-lg bg-accent-50 p-3 text-sm text-accent-700">
                    Raised from Inspection #{{ $inspection->id }} ({{ $inspection->inspection_date->format('d M, Y') }})
                </div>
            @endif
            <div class="col-span-2">
                <x-input label="Area" name="area" wire:model="area" />
            </div>
            <div class="col-span-2">
                <x-textarea label="Issue *" name="issue" rows="3" wire:model="issue" />
            </div>
            <div class="col-span-2">
                <x-input label="Deadline" name="deadline" type="date" wire:model="deadline" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $snag ? 'Save changes' : 'Create snag' }}</x-button>
            <x-button variant="secondary" href="{{ $snag ? route('snags.show', $snag) : route('snags.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
