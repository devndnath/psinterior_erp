<div>
    <x-page-header title="Assignment">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Subcontractors' => route('subcontractors.index'), $subcontractorAssignment->subcontractor?->name => route('subcontractors.show', $subcontractorAssignment->subcontractor), 'Assignment' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('delete', $subcontractorAssignment)
                <x-confirm-dialog
                    title="Remove this assignment?"
                    message="This will remove the assignment and its payment history. This cannot be undone."
                    action="deleteAssignment"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Remove</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $subcontractorAssignment->subcontractor?->name }} on {{ $subcontractorAssignment->project?->project_code }} — {{ $subcontractorAssignment->project?->name }}
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Work Description</h2>
                <p class="whitespace-pre-line text-sm text-ink-900">{{ $subcontractorAssignment->work_description ?: 'No description provided.' }}</p>
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Progress</h2>
                    @can('update', $subcontractorAssignment)
                        <button type="button" wire:click="toggleProgressForm" class="text-sm text-accent-600 hover:underline">
                            {{ $showProgressForm ? 'Cancel' : 'Update Progress' }}
                        </button>
                    @endcan
                </div>
                <x-progress-bar :value="$subcontractorAssignment->progress" />

                @if ($showProgressForm)
                    <form wire:submit="updateProgress" class="mt-4 flex items-end gap-3">
                        <div class="flex-1">
                            <x-input label="Progress (%)" name="progress" type="number" min="0" max="100" wire:model="progress" />
                        </div>
                        <x-button type="submit">Save</x-button>
                    </form>
                @endif
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Payments</h2>
                    @can('create', \App\Models\SubcontractorPayment::class)
                        <button type="button" wire:click="togglePaymentForm" class="text-sm text-accent-600 hover:underline">
                            {{ $showPaymentForm ? 'Cancel' : '+ Record Payment' }}
                        </button>
                    @endcan
                </div>

                @if ($showPaymentForm)
                    <form wire:submit="recordPayment" class="mb-4 grid grid-cols-3 gap-3 rounded-lg border border-ink-100 p-4">
                        <x-input label="Date *" name="payment_date" type="date" wire:model="payment_date" />
                        <x-input label="Amount *" name="payment_amount" type="number" step="0.01" wire:model="payment_amount" />
                        <x-input label="Notes" name="payment_notes" wire:model="payment_notes" />
                        <div class="col-span-3">
                            <x-button type="submit">Save payment</x-button>
                        </div>
                    </form>
                @endif

                @if ($subcontractorAssignment->payments->isEmpty())
                    <p class="text-sm text-ink-500">No payments recorded yet.</p>
                @else
                    <x-table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-right">Amount</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subcontractorAssignment->payments as $payment)
                                <tr wire:key="payment-{{ $payment->id }}">
                                    <td class="text-ink-900">{{ $payment->date->format('d M, Y') }}</td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="text-ink-500">{{ $payment->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Financials</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between"><dt class="text-ink-500">Rate</dt><dd class="font-medium text-ink-900">{{ number_format($subcontractorAssignment->rate, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-ink-500">Total Paid</dt><dd class="font-medium text-ink-900">{{ number_format($totalPaid, 2) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
