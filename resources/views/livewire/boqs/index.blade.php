<div>
    <x-breadcrumbs :items="['BOQs' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Bills of Quantities</h1>
        @can('create', \App\Models\Boq::class)
            <a href="{{ route('boqs.create') }}" wire:navigate class="btn-primary">+ New BOQ</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="BOQ no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
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
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($boqs->isEmpty())
            <x-empty-state
                title="No BOQs found"
                description="Create a Bill of Quantities to start estimating a project's costs."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">BOQ #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Version</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Grand Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($boqs as $boq)
                        <tr wire:key="boq-{{ $boq->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('boqs.show', $boq) }}" wire:navigate class="text-brand-600 hover:underline">{{ $boq->boq_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($boq->project)
                                    <a href="{{ route('projects.show', $boq->project) }}" wire:navigate class="hover:underline">{{ $boq->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">v{{ $boq->version }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($boq->grand_total, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$boq->status->label()" :color="$boq->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('boqs.show', $boq) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $boq)
                                    <a href="{{ route('boqs.edit', $boq) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $boq)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this BOQ?"
                                            :message='"This will remove {$boq->boq_no} and its items. This cannot be undone."'
                                            action="deleteBoq"
                                            :params="[$boq->id]"
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
                {{ $boqs->links() }}
            </div>
        @endif
    </div>
</div>
