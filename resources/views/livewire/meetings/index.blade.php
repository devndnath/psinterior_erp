<div>
    <x-page-header title="Meetings" description="Capture agendas, discussion points and decisions from project meetings.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Meetings' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Meeting::class)
                <x-button href="{{ route('meetings.create') }}" wire:navigate>+ New Meeting</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($meetings->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No meetings found"
                description="Record a meeting to capture its agenda, discussion and decisions."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Project</th>
                    <th>Location</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($meetings as $meeting)
                    <tr wire:key="meeting-{{ $meeting->id }}">
                        <td>
                            <a href="{{ route('meetings.show', $meeting) }}" wire:navigate class="text-accent-600 hover:underline">{{ $meeting->date->format('d M, Y') }}</a>
                        </td>
                        <td>{{ $meeting->type->label() }}</td>
                        <td>
                            @if ($meeting->project)
                                <a href="{{ route('projects.show', $meeting->project) }}" wire:navigate class="hover:underline">{{ $meeting->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $meeting->location ?: '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('meetings.show', $meeting) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $meeting)
                                <a href="{{ route('meetings.edit', $meeting) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $meeting)
                                                                    <x-confirm-dialog
                                        title="Delete this meeting?"
                                        message="This will remove the meeting and its participant list. This cannot be undone."
                                        action="deleteMeeting"
                                        :params="[$meeting->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="dropdown-item-danger">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                            @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $meetings->links() }}
        </div>
    @endif
</div>
