<div>
    <x-breadcrumbs :items="['Change Orders' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Change Orders</h1>
        @can('create', \App\Models\ChangeOrder::class)
            <a href="{{ route('change-orders.create') }}" wire:navigate class="btn-primary">+ New Change Order</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="CO no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
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
        @if ($changeOrders->isEmpty())
            <x-empty-state
                title="No change orders found"
                description="Create a change order to record additional scope or cost against a project."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">CO #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Cost</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($changeOrders as $changeOrder)
                        <tr wire:key="co-{{ $changeOrder->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('change-orders.show', $changeOrder) }}" wire:navigate class="text-brand-600 hover:underline">{{ $changeOrder->co_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($changeOrder->project)
                                    <a href="{{ route('projects.show', $changeOrder->project) }}" wire:navigate class="hover:underline">{{ $changeOrder->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($changeOrder->description, 60) ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($changeOrder->cost, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$changeOrder->status->label()" :color="$changeOrder->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('change-orders.show', $changeOrder) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $changeOrder)
                                    <a href="{{ route('change-orders.edit', $changeOrder) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $changeOrder)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this change order?"
                                            :message='"This will remove {$changeOrder->co_no} and its items. This cannot be undone."'
                                            action="deleteChangeOrder"
                                            :params="[$changeOrder->id]"
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
                {{ $changeOrders->links() }}
            </div>
        @endif
    </div>
</div>
