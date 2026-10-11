<div>
    <x-page-header title="Material Requisitions" description="Request materials for a project and track approval through to issue.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Material Requisitions' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\MaterialRequisition::class)
                <x-button href="{{ route('material-requisitions.create') }}" wire:navigate>+ New Requisition</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="MR no…" />
        </div>
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($requisitions->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No material requisitions found"
                description="Create a requisition to request materials for a project."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>MR #</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requisitions as $mr)
                    <tr wire:key="mr-{{ $mr->id }}">
                        <td class="font-mono">
                            <a href="{{ route('material-requisitions.show', $mr) }}" wire:navigate class="text-accent-600 hover:underline">{{ $mr->mr_no }}</a>
                        </td>
                        <td class="text-ink-500">
                            @if ($mr->project)
                                <a href="{{ route('projects.show', $mr->project) }}" wire:navigate class="hover:underline">{{ $mr->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-ink-500">{{ $mr->date->format('d M, Y') }}</td>
                        <td>
                            <x-status-badge :label="$mr->status->label()" :color="$mr->status->color()" />
                        </td>
                        <td class="text-right">
                            <a href="{{ route('material-requisitions.show', $mr) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $mr)
                                <a href="{{ route('material-requisitions.edit', $mr) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $requisitions->links() }}
        </div>
    @endif
</div>
