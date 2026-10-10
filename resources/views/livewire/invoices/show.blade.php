<div>
    <x-breadcrumbs :items="['Invoices' => route('invoices.index'), $invoice->invoice_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $invoice->invoice_no }}</h1>
                <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($invoice->project)
                    <a href="{{ route('projects.show', $invoice->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $invoice->project->project_code }}</a>
                    — {{ $invoice->project->name }}
                @endif
                @if ($invoice->client)
                    — {{ $invoice->client->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('issue', $invoice)
                @if ($invoice->status->value === 'draft')
                    <button type="button" wire:click="issueInvoice" class="btn-secondary">Issue Invoice</button>
                @endif
            @endcan
            @can('create', \App\Models\Payment::class)
                <a href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" wire:navigate class="btn-secondary">Record Payment</a>
            @endcan
            @can('update', $invoice)
                <a href="{{ route('invoices.edit', $invoice) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('cancel', $invoice)
                @if ($invoice->status->value !== 'cancelled' && (float) $invoice->paid_amount <= 0)
                    <x-confirm-dialog
                        title="Cancel this invoice?"
                        message="This marks the invoice as cancelled. It can only be done while no payments have been recorded against it."
                        action="cancelInvoice"
                    >
                        <x-slot:trigger>
                            <button type="button" class="btn-secondary text-red-600">Cancel Invoice</button>
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
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900">{{ $invoice->type->label() }}</dd></div>
                    <div><dt class="text-gray-500">Invoice date</dt><dd class="font-medium text-gray-900">{{ $invoice->invoice_date?->format('d M, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Due date</dt><dd class="font-medium text-gray-900">{{ $invoice->due_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Contract</dt>
                        <dd class="font-medium text-gray-900">
                            @if ($invoice->contract)
                                <a href="{{ route('contracts.show', $invoice->contract) }}" wire:navigate class="text-brand-600 hover:underline">{{ $invoice->contract->contract_no }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $invoice->notes ?: '—' }}</dd></div>
                </dl>
            </div>

            @if ($invoice->items->isNotEmpty())
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $item->description }}</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-500">{{ $item->qty }}</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Payments received</h2>
                @if ($invoice->payments->isEmpty())
                    <p class="text-sm text-gray-500">No payments recorded against this invoice yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Payment #</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Method</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($invoice->payments as $payment)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        <a href="{{ route('payments.show', $payment) }}" wire:navigate class="text-brand-600 hover:underline">{{ $payment->payment_no }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $payment->date?->format('d M, Y') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $payment->method->label() }}</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Amounts</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium text-gray-900">{{ number_format($invoice->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="font-medium text-gray-900">-{{ number_format($invoice->discount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd class="font-medium text-gray-900">+{{ number_format($invoice->tax, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-100 pt-2"><dt class="font-medium text-gray-700">Grand total</dt><dd class="font-semibold text-gray-900">{{ number_format($invoice->grand_total, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Paid</dt><dd class="font-medium text-gray-900">{{ number_format($invoice->paid_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="font-medium text-gray-700">Outstanding</dt><dd class="font-semibold text-gray-900">{{ number_format($invoice->outstandingAmount(), 2) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
