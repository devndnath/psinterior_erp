<div>
    <x-page-header :title="$invoice->invoice_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Invoices' => route('client.invoices.index'), $invoice->invoice_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="font-medium text-ink-900">{{ $item->description }}</td>
                        <td class="text-right">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="border-t border-ink-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-ink-500">Grand Total</dt><dd class="font-medium text-ink-900">{{ number_format($invoice->grand_total, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-500">Paid</dt><dd class="font-medium text-ink-900">{{ number_format($invoice->paid_amount, 2) }}</dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-1 text-base"><dt class="font-semibold text-ink-700">Balance Due</dt><dd class="font-semibold text-ink-900">{{ number_format($invoice->outstandingAmount(), 2) }}</dd></div>
            </dl>
        </div>
    </div>
</div>
