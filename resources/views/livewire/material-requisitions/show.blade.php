<div>
    <x-page-header :title="$materialRequisition->mr_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Material Requisitions' => route('material-requisitions.index'), $materialRequisition->mr_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$materialRequisition->status->label()" :color="$materialRequisition->status->color()" />
            @can('update', $materialRequisition)
                <x-button variant="secondary" href="{{ route('material-requisitions.edit', $materialRequisition) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $materialRequisition)
                <x-confirm-dialog
                    title="Delete this requisition?"
                    :message='"This will remove {$materialRequisition->mr_no} and its items. This cannot be undone."'
                    action="deleteRequisition"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($materialRequisition->project)
            <a href="{{ route('projects.show', $materialRequisition->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $materialRequisition->project->project_code }}</a>
            — {{ $materialRequisition->project->name }}
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Date</dt><dd class="font-medium text-ink-900">{{ $materialRequisition->date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">Requested By</dt><dd class="font-medium text-ink-900">{{ $materialRequisition->requestedBy?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Warehouse</dt><dd class="font-medium text-ink-900">{{ $materialRequisition->warehouse?->name ?? 'Not chosen yet' }}</dd></div>
                </dl>
                @if ($materialRequisition->purpose)
                    <p class="mt-4 whitespace-pre-line text-sm text-ink-700">{{ $materialRequisition->purpose }}</p>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Items</h2>
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Requested</th>
                            <th class="text-right">Issued</th>
                            @if ($materialRequisition->status->value === 'issued')
                                <th class="text-right">Returned</th>
                                <th class="text-right">Consumed</th>
                                @can('returnItems', $materialRequisition)
                                    <th class="text-right">Record Return</th>
                                @endcan
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materialRequisition->items as $item)
                            <tr wire:key="mr-item-{{ $item->id }}">
                                <td class="text-ink-900">{{ $item->product?->name }} <span class="text-ink-400">({{ $item->product?->sku }})</span></td>
                                <td class="text-right text-ink-500">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right font-medium text-ink-900">{{ number_format($item->issued_quantity, 2) }}</td>
                                @if ($materialRequisition->status->value === 'issued')
                                    <td class="text-right text-ink-500">{{ number_format($item->returned_quantity, 2) }}</td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($item->consumedQuantity(), 2) }}</td>
                                    @can('returnItems', $materialRequisition)
                                        <td class="text-right">
                                            @if ($item->returnableQuantity() > 0)
                                                <div class="flex items-center justify-end gap-2">
                                                    <input
                                                        type="number" step="0.01" min="0" max="{{ $item->returnableQuantity() }}"
                                                        wire:model="return_quantity.{{ $item->id }}"
                                                        placeholder="Qty"
                                                        class="w-20 rounded-md border-ink-200 bg-surface text-sm text-ink-900 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                                                    >
                                                    <button type="button" wire:click="returnItem({{ $item->id }})" class="btn-secondary px-2 py-1 text-xs">Return</button>
                                                </div>
                                                @error('return_quantity.'.$item->id)
                                                    <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                                                @enderror
                                            @else
                                                <span class="text-ink-400">Fully returned</span>
                                            @endif
                                        </td>
                                    @endcan
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Approval</h2>

                <div class="space-y-3">
                    @can('approveByManager', $materialRequisition)
                        @if ($materialRequisition->status->value === 'pending')
                            <x-confirm-dialog
                                title="Approve as manager?"
                                message="This moves the requisition to Manager Approved."
                                action="approveByManager"
                            >
                                <x-slot:trigger>
                                    <button type="button" class="btn-primary w-full">Manager Approve</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('approveByStore', $materialRequisition)
                        @if ($materialRequisition->status->value === 'manager_approved')
                            <x-confirm-dialog
                                title="Approve as store?"
                                message="This moves the requisition to Store Approved, ready to be issued."
                                action="approveByStore"
                            >
                                <x-slot:trigger>
                                    <button type="button" class="btn-primary w-full">Store Approve</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('reject', $materialRequisition)
                        @if (in_array($materialRequisition->status->value, ['pending', 'manager_approved'], true))
                            <x-confirm-dialog
                                title="Reject this requisition?"
                                message="This marks the requisition as rejected. It cannot be issued afterward."
                                action="reject"
                            >
                                <x-slot:trigger>
                                    <button type="button" class="btn-secondary w-full text-danger-600">Reject</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('issue', $materialRequisition)
                        @if ($materialRequisition->status->value === 'store_approved')
                            @if (! $materialRequisition->warehouse_id)
                                <x-select label="Issue From Warehouse *" name="issue_warehouse_id" wire:model="issue_warehouse_id">
                                    <option value="">Select…</option>
                                    @foreach ($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </x-select>
                            @endif
                            <x-confirm-dialog
                                title="Issue this requisition?"
                                message="This will deduct the full requested quantity of each item from the chosen warehouse's stock. This cannot be undone."
                                action="issue"
                            >
                                <x-slot:trigger>
                                    <button type="button" class="btn-primary w-full">Issue</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @if ($materialRequisition->status->value === 'issued')
                        <p class="text-sm text-ink-500">This requisition has been fully issued.</p>
                    @elseif ($materialRequisition->status->value === 'rejected')
                        <p class="text-sm text-ink-500">This requisition was rejected.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
