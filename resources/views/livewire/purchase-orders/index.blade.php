<div>
    <x-page-header title="Purchase Orders" description="Create purchase orders ad-hoc, or from a selected vendor quotation, and track delivery.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Purchase Orders' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\PurchaseOrder::class)
                <x-button href="{{ route('purchase-orders.create') }}" wire:navigate>+ New Purchase Order</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="PO no…" />
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

    @if ($purchaseOrders->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No purchase orders found"
                description="Create a purchase order ad-hoc, or from a selected vendor quotation on a purchase request."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Vendor</th>
                    <th>Project</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseOrders as $po)
                    <tr wire:key="po-{{ $po->id }}">
                        <td class="font-mono">
                            <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="text-accent-600 hover:underline">{{ $po->po_no }}</a>
                        </td>
                        <td>{{ $po->vendor?->company_name }}</td>
                        <td>
                            @if ($po->project)
                                <a href="{{ route('projects.show', $po->project) }}" wire:navigate class="hover:underline">{{ $po->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="font-medium text-ink-900">{{ number_format($po->total, 2) }}</td>
                        <td>
                            <x-status-badge :label="$po->status->label()" :color="$po->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $po)
                                <a href="{{ route('purchase-orders.edit', $po) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $po)
                                                                    <x-confirm-dialog
                                        title="Delete this purchase order?"
                                        :message='"This will remove {$po->po_no} and its items. This cannot be undone."'
                                        action="deletePurchaseOrder"
                                        :params="[$po->id]"
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
            {{ $purchaseOrders->links() }}
        </div>
    @endif
</div>
