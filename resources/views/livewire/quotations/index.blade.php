<div>
    <x-page-header title="Quotations" description="Price up projects and track client decisions through to approval.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Quotations' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Quotation::class)
                <x-button href="{{ route('quotations.create') }}" wire:navigate>+ New Quotation</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Quotation no…" />
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

    @if ($quotations->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No quotations found"
                description="Create a quotation to send pricing to a client."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Quotation #</th>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Version</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotations as $quotation)
                    <tr wire:key="quotation-{{ $quotation->id }}">
                        <td class="font-mono">
                            <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="text-accent-600 hover:underline">{{ $quotation->quotation_no }}</a>
                        </td>
                        <td>
                            @if ($quotation->project)
                                <a href="{{ route('projects.show', $quotation->project) }}" wire:navigate class="hover:underline">{{ $quotation->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $quotation->client?->name ?? '—' }}</td>
                        <td>v{{ $quotation->version }}</td>
                        <td class="font-medium text-ink-900">{{ number_format($quotation->grand_total, 2) }}</td>
                        <td>
                            <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $quotation)
                                <a href="{{ route('quotations.edit', $quotation) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $quotation)
                                                                    <x-confirm-dialog
                                        title="Delete this quotation?"
                                        :message='"This will remove {$quotation->quotation_no} and its items. This cannot be undone."'
                                        action="deleteQuotation"
                                        :params="[$quotation->id]"
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
            {{ $quotations->links() }}
        </div>
    @endif
</div>
