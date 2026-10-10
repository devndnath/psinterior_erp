<div>
    <x-breadcrumbs :items="['Subcontractors' => route('subcontractors.index'), $subcontractorAssignment->subcontractor?->name => route('subcontractors.show', $subcontractorAssignment->subcontractor), 'Assignment' => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ $subcontractorAssignment->subcontractor?->name }} on {{ $subcontractorAssignment->project?->project_code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">{{ $subcontractorAssignment->project?->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            @can('delete', $subcontractorAssignment)
                <x-confirm-dialog
                    title="Remove this assignment?"
                    message="This will remove the assignment and its payment history. This cannot be undone."
                    action="deleteAssignment"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Remove</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Work Description</h2>
                <p class="whitespace-pre-line text-sm text-gray-900">{{ $subcontractorAssignment->work_description ?: 'No description provided.' }}</p>
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Progress</h2>
                    @can('update', $subcontractorAssignment)
                        <button type="button" wire:click="toggleProgressForm" class="text-sm text-brand-600 hover:underline">
                            {{ $showProgressForm ? 'Cancel' : 'Update Progress' }}
                        </button>
                    @endcan
                </div>
                <x-progress-bar :value="$subcontractorAssignment->progress" />

                @if ($showProgressForm)
                    <form wire:submit="updateProgress" class="mt-4 flex items-end gap-3">
                        <div class="flex-1">
                            <label class="form-label">Progress (%)</label>
                            <input wire:model="progress" type="number" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('progress') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn-primary">Save</button>
                    </form>
                @endif
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Payments</h2>
                    @can('create', \App\Models\SubcontractorPayment::class)
                        <button type="button" wire:click="togglePaymentForm" class="text-sm text-brand-600 hover:underline">
                            {{ $showPaymentForm ? 'Cancel' : '+ Record Payment' }}
                        </button>
                    @endcan
                </div>

                @if ($showPaymentForm)
                    <form wire:submit="recordPayment" class="mb-4 grid grid-cols-3 gap-3 rounded-lg border border-gray-100 p-4">
                        <div>
                            <label class="form-label">Date *</label>
                            <input wire:model="payment_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('payment_date') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Amount *</label>
                            <input wire:model="payment_amount" type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('payment_amount') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <input wire:model="payment_notes" type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('payment_notes') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-3">
                            <button type="submit" class="btn-primary">Save payment</button>
                        </div>
                    </form>
                @endif

                @if ($subcontractorAssignment->payments->isEmpty())
                    <p class="text-sm text-gray-500">No payments recorded yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($subcontractorAssignment->payments as $payment)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $payment->date->format('d M, Y') }}</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $payment->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Financials</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between"><dt class="text-gray-500">Rate</dt><dd class="font-medium text-gray-900">{{ number_format($subcontractorAssignment->rate, 2) }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="text-gray-500">Total Paid</dt><dd class="font-medium text-gray-900">{{ number_format($totalPaid, 2) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
