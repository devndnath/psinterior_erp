<div>
    <x-page-header title="Payments" description="Track money received from clients against invoices and projects.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payments' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Payment::class)
                <x-button href="{{ route('payments.create') }}" wire:navigate>+ Record Payment</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Payment no…" />
        </div>
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">Any project</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Method" name="method" wire:model.live="method">
                <option value="">Any method</option>
                @foreach ($methods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($payments->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No payments found"
                description="Record a payment to track money received from a client."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Payment #</th>
                    <th>Project</th>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr wire:key="payment-{{ $payment->id }}">
                        <td class="font-mono">
                            <a href="{{ route('payments.show', $payment) }}" wire:navigate class="text-accent-600 hover:underline">{{ $payment->payment_no }}</a>
                        </td>
                        <td>
                            @if ($payment->project)
                                <a href="{{ route('projects.show', $payment->project) }}" wire:navigate class="hover:underline">{{ $payment->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if ($payment->invoice)
                                <a href="{{ route('invoices.show', $payment->invoice) }}" wire:navigate class="hover:underline">{{ $payment->invoice->invoice_no }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $payment->date?->format('d M, Y') }}</td>
                        <td>{{ $payment->method->label() }}</td>
                        <td class="font-medium text-ink-900">{{ number_format($payment->amount, 2) }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('payments.show', $payment) }}" wire:navigate class="dropdown-item">View</a>
                            @can('delete', $payment)
                                                                    <x-confirm-dialog
                                        title="Delete this payment?"
                                        :message='"This will remove {$payment->payment_no} and recalculate the linked invoice payment status. This cannot be undone."'
                                        action="deletePayment"
                                        :params="[$payment->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="dropdown-item-danger">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                            @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @endif
</div>
