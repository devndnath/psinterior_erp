<div>
    <x-breadcrumbs :items="['Projects' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Projects</h1>
        @can('create', \App\Models\Project::class)
            <a href="{{ route('projects.create') }}" wire:navigate class="btn-primary">+ New Project</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Name, project code, client…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Project manager</label>
            <select wire:model.live="projectManager" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Anyone</option>
                @foreach ($managers as $manager)
                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($projects->isEmpty())
            <x-empty-state
                title="No projects found"
                description="Open a project directly, or create one from a client's page once they're ready to proceed."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="th-sortable" wire:click="sortBy('project_code')">Project #</th>
                        <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Client</th>
                        <th class="th-sortable" wire:click="sortBy('status')">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Progress</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Manager</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($projects as $project)
                        <tr wire:key="project-{{ $project->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('projects.show', $project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $project->project_code }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $project->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $project->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
                            </td>
                            <td class="px-4 py-3">
                                <x-progress-bar :value="$project->progress" />
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $project->projectManager?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('projects.show', $project) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $project)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this project?"
                                            :message="\"This will remove {$project->name} ({$project->project_code}) and its milestones/tasks. This cannot be undone.\""
                                            action="deleteProject"
                                            :params="[$project->id]"
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
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
