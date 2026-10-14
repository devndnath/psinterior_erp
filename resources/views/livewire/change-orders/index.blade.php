<div>
    <x-page-header title="Change Orders" description="Record additional scope or cost changes against a project.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Change Orders' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\ChangeOrder::class)
                <x-button href="{{ route('change-orders.create') }}" wire:navigate>+ New Change Order</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="CO no…" />
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

    @if ($changeOrders->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No change orders found"
                description="Create a change order to record additional scope or cost against a project."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>CO #</th>
                    <th>Project</th>
                    <th>Description</th>
                    <th>Cost</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($changeOrders as $changeOrder)
                    <tr wire:key="co-{{ $changeOrder->id }}">
                        <td class="font-mono">
                            <a href="{{ route('change-orders.show', $changeOrder) }}" wire:navigate class="text-accent-600 hover:underline">{{ $changeOrder->co_no }}</a>
                        </td>
                        <td>
                            @if ($changeOrder->project)
                                <a href="{{ route('projects.show', $changeOrder->project) }}" wire:navigate class="hover:underline">{{ $changeOrder->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($changeOrder->description, 60) ?: '—' }}</td>
                        <td class="font-medium text-ink-900">{{ number_format($changeOrder->cost, 2) }}</td>
                        <td>
                            <x-status-badge :label="$changeOrder->status->label()" :color="$changeOrder->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('change-orders.show', $changeOrder) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $changeOrder)
                                <a href="{{ route('change-orders.edit', $changeOrder) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $changeOrder)
                                                                    <x-confirm-dialog
                                        title="Delete this change order?"
                                        :message='"This will remove {$changeOrder->co_no} and its items. This cannot be undone."'
                                        action="deleteChangeOrder"
                                        :params="[$changeOrder->id]"
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
            {{ $changeOrders->links() }}
        </div>
    @endif
</div>
