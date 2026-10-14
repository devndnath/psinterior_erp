<div>
    <x-page-header title="Clients" description="Everyone the business has signed up, from one-off homeowners to repeat developers.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Clients' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Client::class)
                <x-button href="{{ route('clients.create') }}" wire:navigate>+ New Client</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name, phone, email, client no…" />
        </div>
        <div>
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">All</option>
                @foreach ($types as $value => $label)
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

    @if ($clients->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No clients found"
                description="Clients are usually created by converting a Won lead, or you can add one directly."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th class="th-sortable" wire:click="sortBy('client_no')">Client #</th>
                    <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Assigned</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients as $client)
                    <tr wire:key="client-{{ $client->id }}">
                        <td class="font-mono">
                            <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-accent-600 hover:underline">{{ $client->client_no }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $client->name }}</td>
                        <td>{{ $client->type->label() }}</td>
                        <td>{{ $client->phone ?: '—' }}</td>
                        <td>{{ $client->assignee?->name ?? '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single
                                 "..." action menu instead of several
                                 separate text links in every row. --}}
                            <x-dropdown>
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="dropdown-item">View</a>
                                @can('update', $client)
                                    <a href="{{ route('clients.edit', $client) }}" wire:navigate class="dropdown-item">Edit</a>
                                @endcan
                                @can('delete', $client)
                                    <x-confirm-dialog
                                        title="Delete this client?"
                                        :message='"This will remove {$client->name} ({$client->client_no}). This cannot be undone."'
                                        action="deleteClient"
                                        :params="[$client->id]"
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
            {{ $clients->links() }}
        </div>
    @endif
</div>
