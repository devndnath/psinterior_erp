<div>
    <x-breadcrumbs :items="['Leads' => route('leads.index'), $lead->lead_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $lead->name }}</h1>
            <p class="mt-1 font-mono text-sm text-gray-500">{{ $lead->lead_no }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-status-badge :label="$lead->priority->label()" :color="$lead->priority->color()" />
            <x-status-badge :label="$lead->status->label()" :color="$lead->status->color()" />
            @can('update', $lead)
                <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @if ($lead->isConverted())
                <a href="{{ route('clients.show', $lead->convertedClient) }}" wire:navigate class="btn-secondary">View client {{ $lead->convertedClient?->client_no }}</a>
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
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Lead details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $lead->phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $lead->email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Company</dt><dd class="font-medium text-gray-900">{{ $lead->company_name ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Project type</dt><dd class="font-medium text-gray-900">{{ $lead->project_type?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Source</dt><dd class="font-medium text-gray-900">{{ $lead->source?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Estimated budget</dt><dd class="font-medium text-gray-900">{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Assigned to</dt><dd class="font-medium text-gray-900">{{ $lead->assignee?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Expected closing</dt><dd class="font-medium text-gray-900">{{ $lead->expected_closing_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $lead->address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $lead->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Follow-up history</h2>

                @if ($followups->isEmpty())
                    <p class="text-sm text-gray-500">No follow-ups logged yet.</p>
                @else
                    <ul class="space-y-4">
                        @foreach ($followups as $followup)
                            <li wire:key="followup-{{ $followup->id }}" class="flex items-start justify-between gap-4 rounded-lg border border-gray-100 p-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $followup->type->label() }} — {{ $followup->date?->format('d M, Y') }}
                                        @if ($followup->time) at {{ $followup->time }} @endif
                                    </p>
                                    @if ($followup->notes)
                                        <p class="mt-1 text-sm text-gray-600">{{ $followup->notes }}</p>
                                    @endif
                                    @if ($followup->next_followup_date)
                                        <p class="mt-1 text-xs text-gray-400">Next follow-up: {{ $followup->next_followup_date->format('d M, Y') }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0">
                                    @if ($followup->status->value === \App\Enums\FollowupStatus::Done->value)
                                        <x-status-badge label="Done" color="green" />
                                    @else
                                        <button type="button" wire:click="markFollowupDone({{ $followup->id }})" class="text-sm text-brand-600 hover:underline">Mark done</button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div>
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Log a follow-up</h2>
                <form wire:submit="addFollowup" class="space-y-4">
                    <div>
                        <label class="form-label">Date *</label>
                        <input wire:model="followup_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        @error('followup_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Time</label>
                        <input wire:model="followup_time" type="time" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        @error('followup_time') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Type *</label>
                        <select wire:model="followup_type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @foreach (\App\Enums\FollowupType::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea wire:model="followup_notes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm"></textarea>
                        @error('followup_notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Next follow-up date</label>
                        <input wire:model="followup_next_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        @error('followup_next_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Assigned to</label>
                        <select wire:model="followup_assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="">Unassigned</option>
                            @foreach (\App\Models\User::staff()->orderBy('name')->get(['id', 'name']) as $assignee)
                                <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                        @error('followup_assigned_to') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full">Log follow-up</button>
                </form>
            </div>
        </div>
    </div>
</div>
