<div>
    <x-breadcrumbs :items="['Contracts' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Contracts</h1>
        @can('create', \App\Models\Contract::class)
            <a href="{{ route('contracts.create') }}" wire:navigate class="btn-primary">+ New Contract</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Contract no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($contracts->isEmpty())
            <x-empty-state
                title="No contracts found"
                description="Create a contract once a quotation has been approved."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Contract #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($contracts as $contract)
                        <tr wire:key="contract-{{ $contract->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="text-brand-600 hover:underline">{{ $contract->contract_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($contract->project)
                                    <a href="{{ route('projects.show', $contract->project) }}" wire:navigate class="hover:underline">{{ $contract->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $contract->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($contract->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$contract->status->label()" :color="$contract->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $contract)
                                    <a href="{{ route('contracts.edit', $contract) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $contract)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this contract?"
                                            :message='"This will remove {$contract->contract_no} and its items. This cannot be undone."'
                                            action="deleteContract"
                                            :params="[$contract->id]"
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
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>
