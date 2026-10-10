<div>
    <x-breadcrumbs :items="['Meetings' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Meetings</h1>
        @can('create', \App\Models\Meeting::class)
            <a href="{{ route('meetings.create') }}" wire:navigate class="btn-primary">+ New Meeting</a>
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
            <label class="form-label">Type</label>
            <select wire:model.live="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($meetings->isEmpty())
            <x-empty-state
                title="No meetings found"
                description="Record a meeting to capture its agenda, discussion and decisions."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Location</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($meetings as $meeting)
                        <tr wire:key="meeting-{{ $meeting->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <a href="{{ route('meetings.show', $meeting) }}" wire:navigate class="text-brand-600 hover:underline">{{ $meeting->date->format('d M, Y') }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $meeting->type->label() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($meeting->project)
                                    <a href="{{ route('projects.show', $meeting->project) }}" wire:navigate class="hover:underline">{{ $meeting->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $meeting->location ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('meetings.show', $meeting) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $meeting)
                                    <a href="{{ route('meetings.edit', $meeting) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $meeting)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this meeting?"
                                            message="This will remove the meeting and its participant list. This cannot be undone."
                                            action="deleteMeeting"
                                            :params="[$meeting->id]"
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
                {{ $meetings->links() }}
            </div>
        @endif
    </div>
</div>
