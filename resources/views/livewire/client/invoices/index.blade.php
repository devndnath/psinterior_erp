<div>
    @if ($invoices->isEmpty())
        <x-empty-state
            title="No invoices yet"
            description="Invoices raised for your projects will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th class="text-right">Grand Total</th>
                    <th class="text-right">Balance Due</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr wire:key="invoice-{{ $invoice->id }}">
                        <td class="font-mono">{{ $invoice->invoice_no }}</td>
                        <td>{{ $invoice->project?->name }}</td>
                        <td><x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" /></td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($invoice->grand_total, 2) }}</td>
                        <td class="text-right">{{ number_format($invoice->outstandingAmount(), 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('client.invoices.show', $invoice) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
</div>
