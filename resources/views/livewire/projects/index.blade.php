<div>
    <x-page-header title="Projects" description="Track every active and completed project from kickoff to handover.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Projects' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Project::class)
                <x-button href="{{ route('projects.create') }}" wire:navigate>+ New Project</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name, project code, client…" />
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Project manager" name="projectManager" wire:model.live="projectManager">
                <option value="">Anyone</option>
                @foreach ($managers as $manager)
                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($projects->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No projects found"
                description="Open a project directly, or create one from a client's page once they're ready to proceed."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th class="th-sortable" wire:click="sortBy('project_code')">Project #</th>
                    <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                    <th>Client</th>
                    <th class="th-sortable" wire:click="sortBy('status')">Status</th>
                    <th>Progress</th>
                    <th>Manager</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr wire:key="project-{{ $project->id }}">
                        <td class="font-mono">
                            <a href="{{ route('projects.show', $project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $project->project_code }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $project->name }}</td>
                        <td>{{ $project->client?->name ?? '—' }}</td>
                        <td>
                            <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
                        </td>
                        <td>
                            <x-progress-bar :value="$project->progress" />
                        </td>
                        <td>{{ $project->projectManager?->name ?? '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('projects.show', $project) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $project)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this project?"
                                        :message='"This will remove {$project->name} ({$project->project_code}) and its milestones/tasks. This cannot be undone."'
                                        action="deleteProject"
                                        :params="[$project->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    @endif
</div>
