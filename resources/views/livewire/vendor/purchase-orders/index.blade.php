<div>
    @if ($purchaseOrders->isEmpty())
        <x-empty-state
            title="No purchase orders yet"
            description="Purchase orders raised against your company will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>PO No</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseOrders as $po)
                    <tr wire:key="po-{{ $po->id }}">
                        <td class="font-mono">{{ $po->po_no }}</td>
                        <td>{{ $po->project?->name ?? '—' }}</td>
                        <td><x-status-badge :label="$po->status->label()" :color="$po->status->color()" /></td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($po->total, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('vendor.purchase-orders.show', $po) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $purchaseOrders->links() }}</div>
    @endif
</div>
