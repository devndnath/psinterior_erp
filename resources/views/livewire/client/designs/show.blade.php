<div>
    <x-breadcrumbs :items="['Designs' => route('client.designs.index'), $design->design_no => null]" class="mb-4" />

    <div class="mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $design->title }}</h1>
            <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
        </div>
        <p class="mt-1 text-sm text-gray-500">{{ $design->project?->name }}</p>
    </div>

    @if ($design->latestRevision())
        <div class="mb-6 card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Latest Revision — v{{ $design->latestRevision()->version }}</h2>
            @if ($design->latestRevision()->file)
                <a href="{{ $design->latestRevision()->file->url() }}" target="_blank" class="btn-secondary">View File</a>
            @endif
            @if ($design->latestRevision()->description)
                <p class="mt-3 text-sm text-gray-600">{{ $design->latestRevision()->description }}</p>
            @endif
        </div>
    @endif

    @if (! in_array($design->status->value, ['approved', 'rejected']))
        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Your Decision</h2>
            <form wire:submit="recordDecision" class="space-y-3">
                <div>
                    <label class="form-label">Decision *</label>
                    <select wire:model="decision" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <option value="">Select…</option>
                        @foreach ($decisionOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('decision') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Comment</label>
                    <textarea wire:model="comment" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                </div>
                <button type="submit" class="btn-primary">Submit Decision</button>
            </form>
        </div>
    @endif
</div>
