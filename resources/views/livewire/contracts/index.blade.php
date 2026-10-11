<div>
    <x-page-header title="Contracts" description="Formalize approved quotations into signed project contracts.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Contracts' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Contract::class)
                <x-button href="{{ route('contracts.create') }}" wire:navigate>+ New Contract</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Contract no…" />
        </div>
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($contracts->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No contracts found"
                description="Create a contract once a quotation has been approved."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Contract #</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contracts as $contract)
                    <tr wire:key="contract-{{ $contract->id }}">
                        <td class="font-mono">
                            <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="text-accent-600 hover:underline">{{ $contract->contract_no }}</a>
                        </td>
                        <td>
                            @if ($contract->project)
                                <a href="{{ route('projects.show', $contract->project) }}" wire:navigate class="hover:underline">{{ $contract->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $contract->client?->name ?? '—' }}</td>
                        <td class="font-medium text-ink-900">{{ number_format($contract->amount, 2) }}</td>
                        <td>
                            <x-status-badge :label="$contract->status->label()" :color="$contract->status->color()" />
                        </td>
                        <td class="text-right">
                            <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $contract)
                                <a href="{{ route('contracts.edit', $contract) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
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
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $contracts->links() }}
        </div>
    @endif
</div>
