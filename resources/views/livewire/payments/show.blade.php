<div>
    <x-breadcrumbs :items="['Payments' => route('payments.index'), $payment->payment_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $payment->payment_no }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                @if ($payment->project)
                    <a href="{{ route('projects.show', $payment->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $payment->project->project_code }}</a>
                    — {{ $payment->project->name }}
                @endif
                @if ($payment->client)
                    — {{ $payment->client->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('delete', $payment)
                <x-confirm-dialog
                    title="Delete this payment?"
                    :message='"This will remove {$payment->payment_no} and recalculate the linked invoice payment status. This cannot be undone."'
                    action="deletePayment"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="max-w-xl card p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Amount</dt><dd class="text-lg font-semibold text-gray-900">{{ number_format($payment->amount, 2) }}</dd></div>
            <div><dt class="text-gray-500">Date</dt><dd class="font-medium text-gray-900">{{ $payment->date?->format('d M, Y') }}</dd></div>
            <div><dt class="text-gray-500">Method</dt><dd class="font-medium text-gray-900">{{ $payment->method->label() }}</dd></div>
            <div><dt class="text-gray-500">Invoice</dt>
                <dd class="font-medium text-gray-900">
                    @if ($payment->invoice)
                        <a href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate class="text-brand-600 hover:underline">{{ $payment->invoice->invoice_no }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div><dt class="text-gray-500">Reference</dt><dd class="font-medium text-gray-900">{{ $payment->reference ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $payment->account ?: '—' }}</dd></div>
            <div class="col-span-2"><dt class="text-gray-500">Notes</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $payment->notes ?: '—' }}</dd></div>
        </dl>
    </div>
</div>
