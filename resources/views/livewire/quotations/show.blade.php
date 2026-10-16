<div>
    <x-page-header :title="$quotation->quotation_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Quotations' => route('quotations.index'), $quotation->quotation_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <span class="text-sm text-ink-400">v{{ $quotation->version }}</span>
            <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            <x-button variant="secondary" href="{{ route('quotations.pdf', [$quotation, 'print' => 1]) }}" target="_blank">Print</x-button>
            @if ($quotation->status->value === 'approved' && $quotation->contracts->isEmpty())
                @can('create', \App\Models\Contract::class)
                    <x-button variant="secondary" href="{{ route('contracts.create', ['quotation' => $quotation->id]) }}" wire:navigate>Create Contract</x-button>
                @endcan
            @endif
            @can('update', $quotation)
                <x-button variant="secondary" href="{{ route('quotations.edit', $quotation) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $quotation)
                <x-confirm-dialog
                    title="Delete this quotation?"
                    :message='"This will remove {$quotation->quotation_no} and its items. This cannot be undone."'
                    action="deleteQuotation"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $quotation->client?->name }}
        @if ($quotation->project)
            — <a href="{{ route('projects.show', $quotation->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $quotation->project->project_code }}</a>
        @endif
        @if ($quotation->boq)
            — from <a href="{{ route('boqs.show', $quotation->boq) }}" wire:navigate class="hover:underline">{{ $quotation->boq->boq_no }}</a>
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            {{-- Line items + totals summary: kept as its existing
                 table-like structure (reskinned to table-base/panel
                 tokens) rather than forced into <x-table>, since the
                 totals <dl> block below the rows isn't a <tbody> row and
                 doesn't fit that component's thead/tbody-only slot
                 contract — same judgment call Phase 15a made for
                 Clients' contact list. --}}
            <div class="panel overflow-hidden">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotation->items as $item)
                            <tr>
                                <td>{{ $item->type->label() }}</td>
                                <td class="font-medium text-ink-900">{{ $item->description }}</td>
                                <td class="text-right">{{ $item->qty }} {{ $item->unit }}</td>
                                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-ink-100 px-4 py-4">
                    <dl class="ml-auto max-w-xs space-y-1 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-medium text-ink-900">{{ number_format($quotation->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-500">Discount</dt><dd class="font-medium text-ink-900">-{{ number_format($quotation->discount, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-500">Tax</dt><dd class="font-medium text-ink-900">+{{ number_format($quotation->tax, 2) }}</dd></div>
                        <div class="flex justify-between border-t border-ink-100 pt-1 text-base"><dt class="font-semibold text-ink-700">Grand Total</dt><dd class="font-semibold text-ink-900">{{ number_format($quotation->grand_total, 2) }}</dd></div>
                    </dl>
                </div>
            </div>

            @if ($quotation->versions->isNotEmpty())
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Revision history</h2>
                    <ul class="space-y-2">
                        @foreach ($quotation->versions as $version)
                            <li class="flex items-center justify-between rounded-lg border border-ink-100 p-3 text-sm">
                                <span class="font-medium text-ink-900">Version {{ $version->version }}</span>
                                <span class="text-ink-500">Grand total {{ number_format($version->snapshot['grand_total'] ?? 0, 2) }} — {{ $version->created_at?->format('d M, Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Status</h2>
                <div class="space-y-2">
                    @can('update', $quotation)
                        @if ($quotation->status->value === 'draft')
                            <x-button variant="secondary" wire:click="changeStatus('sent')" class="w-full">Mark as Sent</x-button>
                        @elseif ($quotation->status->value === 'sent')
                            <x-button variant="secondary" wire:click="changeStatus('viewed')" class="w-full">Mark as Viewed</x-button>
                            <x-button variant="secondary" wire:click="changeStatus('negotiation')" class="w-full">Move to Negotiation</x-button>
                        @elseif (in_array($quotation->status->value, ['viewed', 'negotiation']))
                            <x-button variant="secondary" wire:click="changeStatus('negotiation')" class="w-full">Move to Negotiation</x-button>
                        @endif
                    @endcan

                    @can('recordDecision', $quotation)
                        @if (! $quotation->status->isFinal())
                            <x-button variant="secondary" wire:click="toggleDecisionForm" class="w-full">
                                {{ $showDecisionForm ? 'Cancel' : 'Record Client Decision' }}
                            </x-button>
                        @endif
                    @endcan
                </div>

                @if ($showDecisionForm)
                    <form wire:submit="recordDecision" class="mt-4 space-y-3 border-t border-ink-100 pt-4">
                        <x-select label="Decision *" name="decision" wire:model="decision">
                            <option value="">Select…</option>
                            @foreach ($decisionOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-button type="submit" class="w-full">Save decision</x-button>
                    </form>
                @endif
            </div>

            @if ($quotation->terms || $quotation->notes)
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Terms &amp; Notes</h2>
                    @if ($quotation->terms)
                        <p class="text-sm text-ink-500">Terms</p>
                        <p class="mb-3 whitespace-pre-line text-sm text-ink-900">{{ $quotation->terms }}</p>
                    @endif
                    @if ($quotation->notes)
                        <p class="text-sm text-ink-500">Notes</p>
                        <p class="whitespace-pre-line text-sm text-ink-900">{{ $quotation->notes }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
