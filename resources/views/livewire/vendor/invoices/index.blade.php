<div>
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-ink-500">Invoices you've submitted against your purchase orders.</p>
        <x-button href="{{ route('vendor.invoices.create') }}" wire:navigate>+ Submit Invoice</x-button>
    </div>

    @if ($invoices->isEmpty())
        <x-empty-state
            title="No invoices submitted yet"
            description="Submit an invoice against one of your purchase orders to get paid."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Purchase Order</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr wire:key="vendor-invoice-{{ $invoice->id }}">
                        <td class="font-mono">{{ $invoice->vi_no }}</td>
                        <td>{{ $invoice->purchaseOrder?->po_no ?? '—' }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($invoice->amount, 2) }}</td>
                        <td><x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" /></td>
                        <td class="text-right">
                            <a href="{{ route('vendor.invoices.show', $invoice) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
</div>
