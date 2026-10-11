<div>
    <x-page-header :title="$invoice->invoice_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Invoices' => route('invoices.index'), $invoice->invoice_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            @can('issue', $invoice)
                @if ($invoice->status->value === 'draft')
                    <x-button variant="secondary" wire:click="issueInvoice">Issue Invoice</x-button>
                @endif
            @endcan
            @can('create', \App\Models\Payment::class)
                <x-button variant="secondary" href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" wire:navigate>Record Payment</x-button>
            @endcan
            @can('update', $invoice)
                <x-button variant="secondary" href="{{ route('invoices.edit', $invoice) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('cancel', $invoice)
                @if ($invoice->status->value !== 'cancelled' && (float) $invoice->paid_amount <= 0)
                    <x-confirm-dialog
                        title="Cancel this invoice?"
                        message="This marks the invoice as cancelled. It can only be done while no payments have been recorded against it."
                        action="cancelInvoice"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-secondary text-danger-600">Cancel Invoice</button>
                        </x-slot:trigger>
                    </x-confirm-dialog>
                @endif
            @endcan
            @can('delete', $invoice)
                <x-confirm-dialog
                    title="Delete this invoice?"
                    :message='"This will remove {$invoice->invoice_no} and its items. This cannot be undone."'
                    action="deleteInvoice"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($invoice->project)
            <a href="{{ route('projects.show', $invoice->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $invoice->project->project_code }}</a>
            — {{ $invoice->project->name }}
        @endif
        @if ($invoice->client)
            — {{ $invoice->client->name }}
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Type</dt><dd class="font-medium text-ink-900">{{ $invoice->type->label() }}</dd></div>
                    <div><dt class="text-ink-500">Invoice date</dt><dd class="font-medium text-ink-900">{{ $invoice->invoice_date?->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">Due date</dt><dd class="font-medium text-ink-900">{{ $invoice->due_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Contract</dt>
                        <dd class="font-medium text-ink-900">
                            @if ($invoice->contract)
                                <a href="{{ route('contracts.show', $invoice->contract) }}" wire:navigate class="text-accent-600 hover:underline">{{ $invoice->contract->contract_no }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $invoice->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            @if ($invoice->items->isNotEmpty())
                {{-- Line items table: reskinned to table-base/panel tokens
                     rather than wrapped in <x-table>, consistent with the
                     Quotations/Contracts show-page precedent (Phase 15b). --}}
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Line items</h2>
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
                                    <td class="text-ink-900">{{ $item->description }}</td>
                                    <td class="text-right">{{ $item->qty }}</td>
                                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Payments received</h2>
                @if ($invoice->payments->isEmpty())
                    <p class="text-sm text-ink-500">No payments recorded against this invoice yet.</p>
                @else
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->payments as $payment)
                                <tr>
                                    <td>
                                        <a href="{{ route('payments.show', $payment) }}" wire:navigate class="text-accent-600 hover:underline">{{ $payment->payment_no }}</a>
                                    </td>
                                    <td>{{ $payment->date?->format('d M, Y') }}</td>
                                    <td>{{ $payment->method->label() }}</td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Amounts</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-medium text-ink-900">{{ number_format($invoice->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Discount</dt><dd class="font-medium text-ink-900">-{{ number_format($invoice->discount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Tax</dt><dd class="font-medium text-ink-900">+{{ number_format($invoice->tax, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-medium text-ink-700">Grand total</dt><dd class="font-semibold text-ink-900">{{ number_format($invoice->grand_total, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Paid</dt><dd class="font-medium text-ink-900">{{ number_format($invoice->paid_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="font-medium text-ink-700">Outstanding</dt><dd class="font-semibold text-ink-900">{{ number_format($invoice->outstandingAmount(), 2) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
