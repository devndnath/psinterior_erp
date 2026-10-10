<div>
    <x-breadcrumbs :items="['Material Requisitions' => route('material-requisitions.index'), $materialRequisition->mr_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $materialRequisition->mr_no }}</h1>
                <x-status-badge :label="$materialRequisition->status->label()" :color="$materialRequisition->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($materialRequisition->project)
                    <a href="{{ route('projects.show', $materialRequisition->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $materialRequisition->project->project_code }}</a>
                    — {{ $materialRequisition->project->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $materialRequisition)
                <a href="{{ route('material-requisitions.edit', $materialRequisition) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $materialRequisition)
                <x-confirm-dialog
                    title="Delete this requisition?"
                    :message='"This will remove {$materialRequisition->mr_no} and its items. This cannot be undone."'
                    action="deleteRequisition"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Date</dt><dd class="font-medium text-gray-900">{{ $materialRequisition->date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Requested By</dt><dd class="font-medium text-gray-900">{{ $materialRequisition->requestedBy?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Warehouse</dt><dd class="font-medium text-gray-900">{{ $materialRequisition->warehouse?->name ?? 'Not chosen yet' }}</dd></div>
                </dl>
                @if ($materialRequisition->purpose)
                    <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $materialRequisition->purpose }}</p>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Items</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Requested</th>
                            <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Issued</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($materialRequisition->items as $item)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $item->product?->name }} <span class="text-gray-400">({{ $item->product?->sku }})</span></td>
                                <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->issued_quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Approval</h2>

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
                                    <button type="button" class="btn-secondary w-full text-red-600">Reject</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        @endif
                    @endcan

                    @can('issue', $materialRequisition)
                        @if ($materialRequisition->status->value === 'store_approved')
                            @if (! $materialRequisition->warehouse_id)
                                <div>
                                    <label class="form-label">Issue From Warehouse *</label>
                                    <select wire:model="issue_warehouse_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                        <option value="">Select…</option>
                                        @foreach ($warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('issue_warehouse_id') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
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
                        <p class="text-sm text-gray-500">This requisition has been fully issued.</p>
                    @elseif ($materialRequisition->status->value === 'rejected')
                        <p class="text-sm text-gray-500">This requisition was rejected.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
