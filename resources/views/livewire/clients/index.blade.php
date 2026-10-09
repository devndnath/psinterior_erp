<div>
    <x-breadcrumbs :items="['Clients' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Clients</h1>
        @can('create', \App\Models\Client::class)
            <a href="{{ route('clients.create') }}" wire:navigate class="btn-primary">+ New Client</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Name, phone, email, client no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Type</label>
            <select wire:model.live="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">All</option>
                @foreach ($types as $value => $label)
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
        @if ($clients->isEmpty())
            <x-empty-state
                title="No clients found"
                description="Clients are usually created by converting a Won lead, or you can add one directly."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="th-sortable" wire:click="sortBy('client_no')">Client #</th>
                        <th class="th-sortable" wire:click="sortBy('name')">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Assigned</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($clients as $client)
                        <tr wire:key="client-{{ $client->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-brand-600 hover:underline">{{ $client->client_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $client->type->label() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $client->phone ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $client->assignee?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $client)
                                    <a href="{{ route('clients.edit', $client) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $client)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this client?"
                                            :message="\"This will remove {$client->name} ({$client->client_no}). This cannot be undone.\""
                                            action="deleteClient"
                                            :params="[$client->id]"
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
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
