<div>
    <x-breadcrumbs :items="['Inspections' => route('inspections.index'), $inspection->inspection_date->format('d M, Y') => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">Inspection</h1>
                <x-status-badge :label="$inspection->result->label()" :color="$inspection->result->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($inspection->project)
                    <a href="{{ route('projects.show', $inspection->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $inspection->project->project_code }}</a>
                    — {{ $inspection->project->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('create', \App\Models\Snag::class)
                <a href="{{ route('snags.create', ['inspection' => $inspection->id, 'project' => $inspection->project_id]) }}" wire:navigate class="btn-secondary">Raise Snag</a>
            @endcan
            @can('update', $inspection)
                <a href="{{ route('inspections.edit', $inspection) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $inspection)
                <x-confirm-dialog
                    title="Delete this inspection?"
                    message="This will remove the inspection and its checklist items. This cannot be undone."
                    action="deleteInspection"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Area</dt><dd class="font-medium text-gray-900">{{ $inspection->area ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Inspector</dt><dd class="font-medium text-gray-900">{{ $inspection->inspector?->name ?? '—' }}</dd></div>
                </dl>
                @if ($inspection->notes)
                    <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $inspection->notes }}</p>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Checklist Items</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Item</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Result</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($inspection->items as $item)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $item->checklist_item }}</td>
                                <td class="px-3 py-2 text-sm"><x-status-badge :label="$item->result->label()" :color="$item->result->color()" /></td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ $item->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($inspection->snags->isNotEmpty())
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Snags Raised</h2>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($inspection->snags as $snag)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('snags.show', $snag) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $snag->snag_no }}</a>
                                <x-status-badge :label="$snag->status->label()" :color="$snag->status->color()" />
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
