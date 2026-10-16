<div>
    <x-page-header :title="$payment->payment_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payments' => route('payments.index'), $payment->payment_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            <x-button variant="secondary" href="{{ route('payments.pdf', [$payment, 'print' => 1]) }}" target="_blank">Print</x-button>
            @can('delete', $payment)
                <x-confirm-dialog
                    title="Delete this payment?"
                    :message='"This will remove {$payment->payment_no} and recalculate the linked invoice payment status. This cannot be undone."'
                    action="deletePayment"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($payment->project)
            <a href="{{ route('projects.show', $payment->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $payment->project->project_code }}</a>
            — {{ $payment->project->name }}
        @endif
        @if ($payment->client)
            — {{ $payment->client->name }}
        @endif
    </p>

    {{-- Payment amounts: reskinned to panel/dl tokens rather than
         <x-table>, same reasoning as the amounts summary on
         Invoices/Quotations show pages (Phase 15b). --}}
    <div class="panel max-w-xl p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-ink-500">Amount</dt><dd class="text-lg font-semibold text-ink-900">{{ number_format($payment->amount, 2) }}</dd></div>
            <div><dt class="text-ink-500">Date</dt><dd class="font-medium text-ink-900">{{ $payment->date?->format('d M, Y') }}</dd></div>
            <div><dt class="text-ink-500">Method</dt><dd class="font-medium text-ink-900">{{ $payment->method->label() }}</dd></div>
            <div><dt class="text-ink-500">Invoice</dt>
                <dd class="font-medium text-ink-900">
                    @if ($payment->invoice)
                        <a href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate class="text-accent-600 hover:underline">{{ $payment->invoice->invoice_no }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div><dt class="text-ink-500">Reference</dt><dd class="font-medium text-ink-900">{{ $payment->reference ?: '—' }}</dd></div>
            <div><dt class="text-ink-500">Account</dt><dd class="font-medium text-ink-900">{{ $payment->account ?: '—' }}</dd></div>
            <div class="col-span-2"><dt class="text-ink-500">Notes</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $payment->notes ?: '—' }}</dd></div>
        </dl>
    </div>
</div>
