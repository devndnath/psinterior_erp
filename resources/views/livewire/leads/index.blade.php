<div>
    <x-breadcrumbs :items="['Leads' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Leads</h1>
        @can('create', \App\Models\Lead::class)
            <a href="{{ route('leads.create') }}" wire:navigate class="btn-primary">+ New Lead</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Name, phone, email, lead no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Priority</label>
            <select wire:model.live="priority" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">All</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Assigned to</label>
            <select wire:model.live="assignedTo" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Anyone</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($leads->isEmpty())
            <x-empty-state
                title="No leads found"
                description="Try adjusting your filters, or create a new lead to get started."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="th-sortable" wire:click="sortBy('lead_no')">Lead #</th>
                        <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Source</th>
                        <th class="th-sortable" wire:click="sortBy('priority')">Priority</th>
                        <th class="th-sortable" wire:click="sortBy('status')">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Assigned</th>
                        <th class="th-sortable" wire:click="sortBy('expected_closing_date')">Expected close</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($leads as $lead)
                        <tr wire:key="lead-{{ $lead->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('leads.show', $lead) }}" wire:navigate class="text-brand-600 hover:underline">{{ $lead->lead_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $lead->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $lead->source?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :label="$lead->priority->label()" :color="$lead->priority->color()" />
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :label="$lead->status->label()" :color="$lead->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $lead->assignee?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $lead->expected_closing_date?->format('d M, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('leads.show', $lead) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $lead)
                                    <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $lead)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this lead?"
                                            :message='"This will remove {$lead->name} ({$lead->lead_no}). This cannot be undone."'
                                            action="deleteLead"
                                            :params="[$lead->id]"
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
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>
