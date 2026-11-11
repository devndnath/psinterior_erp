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
                    <div><dt class="text-ink-500">Referred by</dt><dd><x-referrer :model="$lead" /></dd></div>
                    <div><dt class="text-ink-500">Estimated budget</dt><dd class="font-medium text-ink-900">{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : '—' }}</dd></div>
                    <div><dt class="text-ink-500">Assigned to</dt><dd class="font-medium text-ink-900">{{ $lead->assignee?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Expected closing</dt><dd class="font-medium text-ink-900">{{ $lead->expected_closing_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Address</dt><dd class="font-medium text-ink-900">{{ $lead->address ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $lead->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            <livewire:calls.panel :lead="$lead" :key="'calls-lead-'.$lead->id" />
        </div>

        <div class="space-y-6">
            {{-- Quick contact: one tap to call or WhatsApp, and the next scheduled call. --}}
            <div class="panel p-5 sm:p-6">
                <p class="section-title">Contact</p>
                <p class="mt-3 text-lg font-semibold tabular-nums text-ink-900">{{ $lead->phone ?: 'No phone' }}</p>
                @if ($lead->email)
                    <a href="mailto:{{ $lead->email }}" class="block truncate text-sm text-accent-600 hover:underline">{{ $lead->email }}</a>
                @endif
                <x-call-actions :phone="$lead->phone" labelled class="mt-4" />
                <div class="mt-5 border-t border-ink-100 pt-4">
                    <p class="text-xs font-medium text-ink-500">Next follow-up</p>
                    @if ($nextCall)
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-ink-900">{{ $nextCall->type->label() }} · {{ $nextCall->date->format('D, d M') }}</p>
                            <x-call-due :call="$nextCall" />
                        </div>
                        <p class="text-xs text-ink-500">{{ $nextCall->assignee?->name ?? 'Unassigned' }}</p>
                    @else
                        <p class="mt-1 text-sm text-ink-500">Nothing scheduled.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
