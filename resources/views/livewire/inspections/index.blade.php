<div>
    <x-breadcrumbs :items="['Inspections' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Inspections</h1>
        @can('create', \App\Models\Inspection::class)
            <a href="{{ route('inspections.create') }}" wire:navigate class="btn-primary">+ New Inspection</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Result</label>
            <select wire:model.live="result" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any result</option>
                @foreach ($results as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($inspections->isEmpty())
            <x-empty-state
                title="No inspections found"
                description="Record an inspection to track QC results against a project."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Area</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Result</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($inspections as $inspection)
                        <tr wire:key="inspection-{{ $inspection->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <a href="{{ route('inspections.show', $inspection) }}" wire:navigate class="text-brand-600 hover:underline">{{ $inspection->inspection_date->format('d M, Y') }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($inspection->project)
                                    <a href="{{ route('projects.show', $inspection->project) }}" wire:navigate class="hover:underline">{{ $inspection->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $inspection->area ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$inspection->result->label()" :color="$inspection->result->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('inspections.show', $inspection) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $inspection)
                                    <a href="{{ route('inspections.edit', $inspection) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $inspection)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this inspection?"
                                            message="This will remove the inspection and its checklist items. This cannot be undone."
                                            action="deleteInspection"
                                            :params="[$inspection->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $inspections->links() }}
            </div>
        @endif
    </div>
</div>
