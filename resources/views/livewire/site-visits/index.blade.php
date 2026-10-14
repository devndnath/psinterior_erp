<div>
    <x-page-header title="Site Visits" description="Log room measurements and requirements captured on-site.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Site Visits' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\SiteVisit::class)
                <x-button href="{{ route('site-visits.create') }}" wire:navigate>+ New Site Visit</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Visit no, client, address…" />
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

    @if ($visits->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No site visits found"
                description="Log a site visit to start capturing room measurements for a client."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th class="th-sortable" wire:click="sortBy('sv_no')">Visit #</th>
                    <th>Client</th>
                    <th class="th-sortable" wire:click="sortBy('visit_date')">Date</th>
                    <th>Purpose</th>
                    <th>Assigned</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($visits as $visit)
                    <tr wire:key="visit-{{ $visit->id }}">
                        <td class="font-mono">
                            <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="text-accent-600 hover:underline">{{ $visit->sv_no }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $visit->client?->name ?? '—' }}</td>
                        <td>{{ $visit->visit_date?->format('d M, Y') }}</td>
                        <td>{{ $visit->purpose ?: '—' }}</td>
                        <td>{{ $visit->assignee?->name ?? '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $visit)
                                <a href="{{ route('site-visits.edit', $visit) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $visit)
                                                                    <x-confirm-dialog
                                        title="Delete this site visit?"
                                        :message='"This will remove visit {$visit->sv_no}. This cannot be undone."'
                                        action="deleteSiteVisit"
                                        :params="[$visit->id]"
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
            {{ $visits->links() }}
        </div>
    @endif
</div>
