<div>
    <x-breadcrumbs :items="['Snags' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Snags</h1>
        @can('create', \App\Models\Snag::class)
            <a href="{{ route('snags.create') }}" wire:navigate class="btn-primary">+ New Snag</a>
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
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Assigned To</label>
            <select wire:model.live="assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Anyone</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($snags->isEmpty())
            <x-empty-state
                title="No snags found"
                description="Raise a snag to track a defect through to resolution."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Snag #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Issue</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Assigned To</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($snags as $snag)
                        <tr wire:key="snag-{{ $snag->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('snags.show', $snag) }}" wire:navigate class="text-brand-600 hover:underline">{{ $snag->snag_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($snag->project)
                                    <a href="{{ route('projects.show', $snag->project) }}" wire:navigate class="hover:underline">{{ $snag->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($snag->issue, 60) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $snag->assignedTo?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$snag->status->label()" :color="$snag->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('snags.show', $snag) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('delete', $snag)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this snag?"
                                            :message='"This will remove {$snag->snag_no}. This cannot be undone."'
                                            action="deleteSnag"
                                            :params="[$snag->id]"
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
                {{ $snags->links() }}
            </div>
        @endif
    </div>
</div>
