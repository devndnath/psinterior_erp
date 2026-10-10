<div>
    <x-breadcrumbs :items="['Quotations' => route('client.quotations.index'), $quotation->quotation_no => null]" class="mb-4" />

    <div class="mb-6 flex items-center gap-3">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $quotation->quotation_no }}</h1>
        <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
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
                @foreach ($quotation->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->description }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $item->qty }} {{ $item->unit }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-gray-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between border-t border-gray-100 pt-1 text-base"><dt class="font-semibold text-gray-700">Grand Total</dt><dd class="font-semibold text-gray-900">{{ number_format($quotation->grand_total, 2) }}</dd></div>
            </dl>
        </div>
    </div>

    @if (! $quotation->status->isFinal())
        <div class="mt-6 card p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Your Decision</h2>
            <form wire:submit="recordDecision" class="space-y-3">
                <div>
                    <label class="form-label">Decision *</label>
                    <select wire:model="decision" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <option value="">Select…</option>
                        @foreach ($decisionOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('decision') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary">Submit Decision</button>
            </form>
        </div>
    @endif
</div>
