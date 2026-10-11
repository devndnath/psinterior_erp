<div>
    <x-page-header :title="$meeting->type->label()" :description="$meeting->date->format('d M, Y')">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Meetings' => route('meetings.index'), $meeting->date->format('d M, Y') => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('update', $meeting)
                <x-button variant="secondary" href="{{ route('meetings.edit', $meeting) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $meeting)
                <x-confirm-dialog
                    title="Delete this meeting?"
                    message="This will remove the meeting and its participant list. This cannot be undone."
                    action="deleteMeeting"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($meeting->project)
            <a href="{{ route('projects.show', $meeting->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $meeting->project->project_code }}</a>
            — {{ $meeting->project->name }}
        @else
            No project
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Time</dt><dd class="font-medium text-ink-900">{{ $meeting->time ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Location</dt><dd class="font-medium text-ink-900">{{ $meeting->location ?: '—' }}</dd></div>
                </dl>
                <div class="mt-4 space-y-3 text-sm">
                    <div><dt class="font-medium text-ink-500">Agenda</dt><dd class="whitespace-pre-line text-ink-900">{{ $meeting->agenda ?: '—' }}</dd></div>
                    <div><dt class="font-medium text-ink-500">Discussion</dt><dd class="whitespace-pre-line text-ink-900">{{ $meeting->discussion ?: '—' }}</dd></div>
                    <div><dt class="font-medium text-ink-500">Decisions</dt><dd class="whitespace-pre-line text-ink-900">{{ $meeting->decisions ?: '—' }}</dd></div>
                    <div><dt class="font-medium text-ink-500">Follow-up</dt><dd class="whitespace-pre-line text-ink-900">{{ $meeting->follow_up ?: '—' }}</dd></div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Participants</h2>
                @if ($meeting->participants->isEmpty())
                    <p class="text-sm text-ink-500">No participants recorded.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($meeting->participants as $participant)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <span class="text-ink-900">{{ $participant->participantName() }}</span>
                                <x-status-badge :label="$participant->participantTypeLabel()" color="gray" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
