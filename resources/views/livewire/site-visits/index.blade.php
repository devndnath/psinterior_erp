<div>
    <x-breadcrumbs :items="['Site Visits' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Site Visits</h1>
        @can('create', \App\Models\SiteVisit::class)
            <a href="{{ route('site-visits.create') }}" wire:navigate class="btn-primary">+ New Site Visit</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Visit no, client, address…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
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
        @if ($visits->isEmpty())
            <x-empty-state
                title="No site visits found"
                description="Log a site visit to start capturing room measurements for a client."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="th-sortable" wire:click="sortBy('sv_no')">Visit #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Client</th>
                        <th class="th-sortable" wire:click="sortBy('visit_date')">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Purpose</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Assigned</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($visits as $visit)
                        <tr wire:key="visit-{{ $visit->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="text-brand-600 hover:underline">{{ $visit->sv_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $visit->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $visit->visit_date?->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $visit->purpose ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $visit->assignee?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $visit)
                                    <a href="{{ route('site-visits.edit', $visit) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $visit)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this site visit?"
                                            :message='"This will remove visit {$visit->sv_no}. This cannot be undone."'
                                            action="deleteSiteVisit"
                                            :params="[$visit->id]"
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
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</div>
