<div>
    <x-page-header :title="$changeOrder->co_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Change Orders' => route('client.change-orders.index'), $changeOrder->co_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$changeOrder->status->label()" :color="$changeOrder->status->color()" />
        </x-slot:actions>
    </x-page-header>

    <div class="panel p-6">
        <p class="text-sm text-ink-500">{{ $changeOrder->description }}</p>
        <p class="mt-4 text-2xl font-semibold text-ink-900">{{ number_format($changeOrder->cost, 2) }}</p>
    </div>

    @if ($changeOrder->items->isNotEmpty())
        <div class="mt-6 panel overflow-hidden">
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
                    @foreach ($changeOrder->items as $item)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $item->description }}</td>
                            <td class="text-right">{{ $item->qty }}</td>
                            <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                            <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($changeOrder->status->value === 'pending')
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
