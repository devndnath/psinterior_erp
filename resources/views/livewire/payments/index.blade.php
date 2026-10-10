<div>
    <x-breadcrumbs :items="['Payments' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Payments</h1>
        @can('create', \App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" wire:navigate class="btn-primary">+ Record Payment</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Payment no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Project</label>
            <select wire:model.live="project" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Method</label>
            <select wire:model.live="method" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any method</option>
                @foreach ($methods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($payments->isEmpty())
            <x-empty-state
                title="No payments found"
                description="Record a payment to track money received from a client."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Payment #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('payments.show', $payment) }}" wire:navigate class="text-brand-600 hover:underline">{{ $payment->payment_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($payment->project)
                                    <a href="{{ route('projects.show', $payment->project) }}" wire:navigate class="hover:underline">{{ $payment->project->project_code }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($payment->invoice)
                                    <a href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate class="hover:underline">{{ $payment->invoice->invoice_no }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $payment->date?->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $payment->method->label() }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('payments.show', $payment) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('delete', $payment)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this payment?"
                                            :message='"This will remove {$payment->payment_no} and recalculate the linked invoice payment status. This cannot be undone."'
                                            action="deletePayment"
                                            :params="[$payment->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
