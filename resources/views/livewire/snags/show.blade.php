<div>
    <x-page-header :title="$snag->snag_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Snags' => route('snags.index'), $snag->snag_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$snag->status->label()" :color="$snag->status->color()" />
            @can('update', $snag)
                <x-button variant="secondary" href="{{ route('snags.edit', $snag) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $snag)
                <x-confirm-dialog
                    title="Delete this snag?"
                    :message='"This will remove {$snag->snag_no}. This cannot be undone."'
                    action="deleteSnag"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($snag->project)
            <a href="{{ route('projects.show', $snag->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $snag->project->project_code }}</a>
            — {{ $snag->project->name }}
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Area</dt><dd class="font-medium text-ink-900">{{ $snag->area ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Deadline</dt><dd class="font-medium text-ink-900">{{ $snag->deadline?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Assigned To</dt><dd class="font-medium text-ink-900">{{ $snag->assignedTo?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">From Inspection</dt><dd class="font-medium text-ink-900">
                        @if ($snag->inspection)
                            <a href="{{ route('inspections.show', $snag->inspection) }}" wire:navigate class="text-accent-600 hover:underline">#{{ $snag->inspection->id }}</a>
                        @else
                            Ad-hoc
                        @endif
                    </dd></div>
                </dl>
                <p class="mt-4 whitespace-pre-line text-sm text-ink-900">{{ $snag->issue }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Workflow</h2>

                <div class="space-y-3">
                    @can('assign', $snag)
                        @if ($snag->status->value === 'pending')
                            <x-select label="Assign To *" name="assign_user_id" wire:model="assign_user_id">
                                <option value="">Select…</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </x-select>
                            <x-confirm-dialog
                                title="Assign this snag?"
                                message="This moves the snag to Assigned."
                                action="assign"
                            >
                                <x-slot:trigger>
                                    <button type="button" class="btn-primary w-full">Assign</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('startProgress', $snag)
                        @if ($snag->status->value === 'assigned')
                            <x-confirm-dialog title="Start progress?" message="This moves the snag to In Progress." action="startProgress">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Start Progress</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('markFixed', $snag)
                        @if ($snag->status->value === 'in_progress')
                            <x-confirm-dialog title="Mark as fixed?" message="This moves the snag to Fixed." action="markFixed">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Mark Fixed</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('verify', $snag)
                        @if ($snag->status->value === 'fixed')
                            <x-confirm-dialog title="Verify this fix?" message="This moves the snag to Verified." action="verify">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Verify</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('close', $snag)
                        @if ($snag->status->value === 'verified')
                            <x-confirm-dialog title="Close this snag?" message="This moves the snag to Closed." action="close">
                                <x-slot:trigger><button type="button" class="btn-primary w-full">Close</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('reopen', $snag)
                        @if ($snag->status->value !== 'closed' && $snag->status->value !== 'pending')
                            <x-confirm-dialog title="Reopen this snag?" message="This moves the snag back to Pending." action="reopen" :danger="false">
                                <x-slot:trigger><button type="button" class="btn-secondary w-full">Reopen</button></x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @if ($snag->status->value === 'closed')
                        <p class="text-sm text-ink-500">This snag is closed.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
