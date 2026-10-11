<div>
    <x-page-header :title="$design->title">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Designs' => route('client.designs.index'), $design->design_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $design->project?->name }}</p>

    @if ($design->latestRevision())
        <div class="mb-6 panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Latest Revision — v{{ $design->latestRevision()->version }}</h2>
            @if ($design->latestRevision()->file)
                <x-button variant="secondary" href="{{ $design->latestRevision()->file->url() }}" target="_blank">View File</x-button>
            @endif
            @if ($design->latestRevision()->description)
                <p class="mt-3 text-sm text-ink-600">{{ $design->latestRevision()->description }}</p>
            @endif
        </div>
    @endif

    @if (! in_array($design->status->value, ['approved', 'rejected']))
        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Your Decision</h2>
            <form wire:submit="recordDecision" class="space-y-3">
                <x-select label="Decision *" name="decision" wire:model="decision">
                    <option value="">Select…</option>
                    @foreach ($decisionOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-textarea label="Comment" name="comment" rows="3" wire:model="comment" />
                <x-button type="submit">Submit Decision</x-button>
            </form>
        </div>
    @endif
</div>
