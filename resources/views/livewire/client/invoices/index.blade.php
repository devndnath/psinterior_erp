<div>
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Invoice No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Grand Total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Balance Due</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $invoice->invoice_no }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $invoice->project?->name }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" /></td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($invoice->grand_total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($invoice->outstandingAmount(), 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('client.invoices.show', $invoice) }}" wire:navigate class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-400">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
