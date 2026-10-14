<div>
    <x-page-header :title="$quotation->quotation_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Quotations' => route('client.quotations.index'), $quotation->quotation_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
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
                @foreach ($quotation->items as $item)
                    <tr>
                        <td class="font-medium text-ink-900">{{ $item->description }}</td>
                        <td class="text-right">{{ $item->qty }} {{ $item->unit }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="border-t border-ink-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between border-t border-ink-100 pt-1 text-base"><dt class="font-semibold text-ink-700">Grand Total</dt><dd class="font-semibold text-ink-900">{{ number_format($quotation->grand_total, 2) }}</dd></div>
            </dl>
        </div>
    </div>

    @if (! $quotation->status->isFinal())
        <div class="mt-6 panel p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Your Decision</h2>
            <form wire:submit="recordDecision" class="space-y-3">
                <x-select label="Decision *" name="decision" wire:model="decision">
                    <option value="">Select…</option>
                    @foreach ($decisionOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-button type="submit">Submit Decision</x-button>
            </form>
        </div>
    @endif
</div>
