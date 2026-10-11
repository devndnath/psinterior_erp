<div>
    <x-page-header title="Inspection">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Inspections' => route('inspections.index'), $inspection->inspection_date->format('d M, Y') => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$inspection->result->label()" :color="$inspection->result->color()" />
            @can('create', \App\Models\Snag::class)
                <x-button variant="secondary" href="{{ route('snags.create', ['inspection' => $inspection->id, 'project' => $inspection->project_id]) }}" wire:navigate>Raise Snag</x-button>
            @endcan
            @can('update', $inspection)
                <x-button variant="secondary" href="{{ route('inspections.edit', $inspection) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $inspection)
                <x-confirm-dialog
                    title="Delete this inspection?"
                    message="This will remove the inspection and its checklist items. This cannot be undone."
                    action="deleteInspection"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($inspection->project)
            <a href="{{ route('projects.show', $inspection->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $inspection->project->project_code }}</a>
            — {{ $inspection->project->name }}
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Area</dt><dd class="font-medium text-ink-900">{{ $inspection->area ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Inspector</dt><dd class="font-medium text-ink-900">{{ $inspection->inspector?->name ?? '—' }}</dd></div>
                </dl>
                @if ($inspection->notes)
                    <p class="mt-4 whitespace-pre-line text-sm text-ink-700">{{ $inspection->notes }}</p>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Checklist Items</h2>
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Result</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inspection->items as $item)
                            <tr>
                                <td class="font-medium text-ink-900">{{ $item->checklist_item }}</td>
                                <td><x-status-badge :label="$item->result->label()" :color="$item->result->color()" /></td>
                                <td>{{ $item->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($inspection->snags->isNotEmpty())
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Snags Raised</h2>
                    <ul class="divide-y divide-ink-100">
                        @foreach ($inspection->snags as $snag)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('snags.show', $snag) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $snag->snag_no }}</a>
                                <x-status-badge :label="$snag->status->label()" :color="$snag->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
