<div>
    <x-page-header :title="$lead->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leads' => route('leads.index'), $lead->lead_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$lead->priority->label()" :color="$lead->priority->color()" />
            <x-status-badge :label="$lead->status->label()" :color="$lead->status->color()" />
            @can('update', $lead)
                <x-button variant="secondary" href="{{ route('leads.edit', $lead) }}" wire:navigate>Edit</x-button>
            @endcan
            @if ($lead->isConverted())
                <x-button variant="secondary" href="{{ route('clients.show', $lead->convertedClient) }}" wire:navigate>View client {{ $lead->convertedClient?->client_no }}</x-button>
                @if ($lead->hasProject())
                    <x-button variant="secondary" href="{{ route('projects.show', $lead->converted_project_id) }}" wire:navigate>View project</x-button>
                @elseif (\Illuminate\Support\Facades\Gate::check('create', \App\Models\Project::class))
                    <x-button href="{{ route('projects.create', ['client' => $lead->converted_client_id, 'lead' => $lead->id]) }}" wire:navigate>Create Project</x-button>
                @endif
            @elseif ($lead->status->value === \App\Enums\LeadStatus::Won->value)
                @can('convert', $lead)
                    <x-confirm-dialog
                        title="Convert to client?"
                        message="This creates a new client record from this lead's details. You can edit the client afterwards."
                        action="convertToClient"
                        confirm-text="Convert"
                        :danger="false"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-primary">Convert to Client</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 font-mono text-sm text-ink-500">{{ $lead->lead_no }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Lead details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Phone</dt><dd class="font-medium text-ink-900">{{ $lead->phone ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Email</dt><dd class="font-medium text-ink-900">{{ $lead->email ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Company</dt><dd class="font-medium text-ink-900">{{ $lead->company_name ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Project type</dt><dd class="font-medium text-ink-900">{{ $lead->project_type?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Source</dt><dd class="font-medium text-ink-900">{{ $lead->source?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Estimated budget</dt><dd class="font-medium text-ink-900">{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : '—' }}</dd></div>
                    <div><dt class="text-ink-500">Assigned to</dt><dd class="font-medium text-ink-900">{{ $lead->assignee?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Expected closing</dt><dd class="font-medium text-ink-900">{{ $lead->expected_closing_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Address</dt><dd class="font-medium text-ink-900">{{ $lead->address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $lead->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Follow-up history</h2>

                @if ($followups->isEmpty())
                    <p class="text-sm text-ink-500">No follow-ups logged yet.</p>
                @else
                    <ul class="space-y-4">
                        @foreach ($followups as $followup)
                            <li wire:key="followup-{{ $followup->id }}" class="flex items-start justify-between gap-4 rounded-lg border border-ink-100 p-3">
                                <div>
                                    <p class="text-sm font-medium text-ink-900">
                                        {{ $followup->type->label() }} — {{ $followup->date?->format('d M, Y') }}
                                        @if ($followup->time) at {{ $followup->time }} @endif
                                    </p>
                                    @if ($followup->notes)
                                        <p class="mt-1 text-sm text-ink-600">{{ $followup->notes }}</p>
                                    @endif
                                    @if ($followup->next_followup_date)
                                        <p class="mt-1 text-xs text-ink-400">Next follow-up: {{ $followup->next_followup_date->format('d M, Y') }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0">
                                    @if ($followup->status->value === \App\Enums\FollowupStatus::Done->value)
                                        <x-status-badge label="Done" color="green" />
                                    @else
                                        <button type="button" wire:click="markFollowupDone({{ $followup->id }})" class="text-sm text-accent-600 hover:underline">Mark done</button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div>
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Log a follow-up</h2>
                <form wire:submit="addFollowup" class="space-y-4">
                    <x-input label="Date *" name="followup_date" type="date" wire:model="followup_date" />
                    <x-input label="Time" name="followup_time" type="time" wire:model="followup_time" />
                    <x-select label="Type *" name="followup_type" wire:model="followup_type">
                        @foreach (\App\Enums\FollowupType::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-textarea label="Notes" name="followup_notes" rows="3" wire:model="followup_notes" />
                    <x-input label="Next follow-up date" name="followup_next_date" type="date" wire:model="followup_next_date" />
                    <x-select label="Assigned to" name="followup_assigned_to" wire:model="followup_assigned_to">
                        <option value="">Unassigned</option>
                        @foreach (\App\Models\User::staff()->orderBy('name')->get(['id', 'name']) as $assignee)
                            <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                        @endforeach
                    </x-select>
                    <x-button type="submit" class="w-full">Log follow-up</x-button>
                </form>
            </div>
        </div>
    </div>
</div>
