<div>
    <x-page-header title="New Service Request">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warranties' => route('warranties.index'), 'Warranty' => route('warranties.show', $warranty), 'New Service Request' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-2 gap-4 p-6">
            <div class="col-span-2 rounded-lg bg-accent-50 p-3 text-sm text-accent-700">
                Against warranty for {{ $warranty->project?->project_code }} — {{ $warranty->project?->name }}
                ({{ $warranty->start_date->format('d M, Y') }} – {{ $warranty->end_date->format('d M, Y') }})
            </div>
            <div class="col-span-2">
                <x-select label="Client *" name="client_id" wire:model="client_id">
                    <option value="">Select…</option>
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="col-span-2">
                <x-textarea label="Complaint *" name="complaint" rows="4" wire:model="complaint" />
            </div>
        </div>

        <p class="text-xs text-ink-400">No client portal yet — this is recorded by staff on the client's behalf.</p>

        <div class="flex items-center gap-3">
            <x-button type="submit">Create Service Request</x-button>
            <x-button variant="secondary" href="{{ route('warranties.show', $warranty) }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
