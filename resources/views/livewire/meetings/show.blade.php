<div>
    <x-breadcrumbs :items="['Meetings' => route('meetings.index'), $meeting->date->format('d M, Y') => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $meeting->type->label() }}</h1>
                <span class="text-sm text-gray-500">{{ $meeting->date->format('d M, Y') }}</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($meeting->project)
                    <a href="{{ route('projects.show', $meeting->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $meeting->project->project_code }}</a>
                    — {{ $meeting->project->name }}
                @else
                    No project
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $meeting)
                <a href="{{ route('meetings.edit', $meeting) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $meeting)
                <x-confirm-dialog
                    title="Delete this meeting?"
                    message="This will remove the meeting and its participant list. This cannot be undone."
                    action="deleteMeeting"
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
                    <div><dt class="text-gray-500">Time</dt><dd class="font-medium text-gray-900">{{ $meeting->time ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Location</dt><dd class="font-medium text-gray-900">{{ $meeting->location ?: '—' }}</dd></div>
                </dl>
                <div class="mt-4 space-y-3 text-sm">
                    <div><dt class="font-medium text-gray-500">Agenda</dt><dd class="whitespace-pre-line text-gray-900">{{ $meeting->agenda ?: '—' }}</dd></div>
                    <div><dt class="font-medium text-gray-500">Discussion</dt><dd class="whitespace-pre-line text-gray-900">{{ $meeting->discussion ?: '—' }}</dd></div>
                    <div><dt class="font-medium text-gray-500">Decisions</dt><dd class="whitespace-pre-line text-gray-900">{{ $meeting->decisions ?: '—' }}</dd></div>
                    <div><dt class="font-medium text-gray-500">Follow-up</dt><dd class="whitespace-pre-line text-gray-900">{{ $meeting->follow_up ?: '—' }}</dd></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Participants</h2>
                @if ($meeting->participants->isEmpty())
                    <p class="text-sm text-gray-500">No participants recorded.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($meeting->participants as $participant)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <span class="text-gray-900">{{ $participant->participantName() }}</span>
                                <x-status-badge :label="$participant->participantTypeLabel()" color="gray" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
