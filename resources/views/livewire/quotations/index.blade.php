<div>
    <x-breadcrumbs :items="['Quotations' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Quotations</h1>
        @can('create', \App\Models\Quotation::class)
            <a href="{{ route('quotations.create') }}" wire:navigate class="btn-primary">+ New Quotation</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Quotation no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
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
        @if ($quotations->isEmpty())
            <x-empty-state
                title="No quotations found"
                description="Create a quotation to send pricing to a client."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Quotation #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Version</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Grand Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($quotations as $quotation)
                        <tr wire:key="quotation-{{ $quotation->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="text-brand-600 hover:underline">{{ $quotation->quotation_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($quotation->project)
                                    <a href="{{ route('projects.show', $quotation->project) }}" wire:navigate class="hover:underline">{{ $quotation->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $quotation->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">v{{ $quotation->version }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($quotation->grand_total, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $quotation)
                                    <a href="{{ route('quotations.edit', $quotation) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $quotation)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this quotation?"
                                            :message="\"This will remove {$quotation->quotation_no} and its items. This cannot be undone.\""
                                            action="deleteQuotation"
                                            :params="[$quotation->id]"
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
                {{ $quotations->links() }}
            </div>
        @endif
    </div>
</div>
