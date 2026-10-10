<div>
    <x-breadcrumbs :items="['Invoices' => route('client.invoices.index'), $invoice->invoice_no => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $invoice->invoice_no }}</h1>
            <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
        </div>
        <button type="button" wire:click="downloadPdf" class="btn-secondary">Download PDF</button>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->description }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $item->qty }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-gray-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Grand Total</dt><dd class="font-medium text-gray-900">{{ number_format($invoice->grand_total, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Paid</dt><dd class="font-medium text-gray-900">{{ number_format($invoice->paid_amount, 2) }}</dd></div>
                <div class="flex justify-between border-t border-gray-100 pt-1 text-base"><dt class="font-semibold text-gray-700">Balance Due</dt><dd class="font-semibold text-gray-900">{{ number_format($invoice->outstandingAmount(), 2) }}</dd></div>
            </dl>
        </div>
    </div>
</div>
