<div>
    <x-page-header title="Leads" description="Track incoming enquiries from first contact to conversion.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leads' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Lead::class)
                <x-button href="{{ route('leads.create') }}" wire:navigate>+ New Lead</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name, phone, email, lead no…" />
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Priority" name="priority" wire:model.live="priority">
                <option value="">All</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Assigned to" name="assignedTo" wire:model.live="assignedTo">
                <option value="">Anyone</option>
                @foreach ($assignees as $assignee)
                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($leads->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No leads found"
                description="Try adjusting your filters, or create a new lead to get started."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th class="th-sortable" wire:click="sortBy('lead_no')">Lead #</th>
                    <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                    <th>Source</th>
                    <th class="th-sortable" wire:click="sortBy('priority')">Priority</th>
                    <th class="th-sortable" wire:click="sortBy('status')">Status</th>
                    <th>Assigned</th>
                    <th class="th-sortable" wire:click="sortBy('expected_closing_date')">Expected close</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leads as $lead)
                    <tr wire:key="lead-{{ $lead->id }}">
                        <td class="font-mono">
                            <a href="{{ route('leads.show', $lead) }}" wire:navigate class="text-accent-600 hover:underline">{{ $lead->lead_no }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $lead->name }}</td>
                        <td>{{ $lead->source?->name ?? '—' }}</td>
                        <td>
                            <x-status-badge :label="$lead->priority->label()" :color="$lead->priority->color()" />
                        </td>
                        <td>
                            <x-status-badge :label="$lead->status->label()" :color="$lead->status->color()" />
                        </td>
                        <td>{{ $lead->assignee?->name ?? '—' }}</td>
                        <td>{{ $lead->expected_closing_date?->format('d M, Y') ?? '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single
                                 "..." action menu instead of several
                                 separate text links in every row. --}}
                            <x-dropdown>
                                <a href="{{ route('leads.show', $lead) }}" wire:navigate class="dropdown-item">View</a>
                                @can('update', $lead)
                                    <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="dropdown-item">Edit</a>
                                @endcan
                                @can('delete', $lead)
                                    <x-confirm-dialog
                                        title="Delete this lead?"
                                        :message='"This will remove {$lead->name} ({$lead->lead_no}). This cannot be undone."'
                                        action="deleteLead"
                                        :params="[$lead->id]"
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
            {{ $leads->links() }}
        </div>
    @endif
</div>
