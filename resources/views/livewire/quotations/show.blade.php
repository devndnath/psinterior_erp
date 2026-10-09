<div>
    <x-breadcrumbs :items="['Quotations' => route('quotations.index'), $quotation->quotation_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $quotation->quotation_no }}</h1>
                <span class="text-sm text-gray-400">v{{ $quotation->version }}</span>
                <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ $quotation->client?->name }}
                @if ($quotation->project)
                    — <a href="{{ route('projects.show', $quotation->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $quotation->project->project_code }}</a>
                @endif
                @if ($quotation->boq)
                    — from <a href="{{ route('boqs.show', $quotation->boq) }}" wire:navigate class="hover:underline">{{ $quotation->boq->boq_no }}</a>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if ($quotation->status->value === 'approved' && $quotation->contracts->isEmpty())
                @can('create', \App\Models\Contract::class)
                    <a href="{{ route('contracts.create', ['quotation' => $quotation->id]) }}" wire:navigate class="btn-secondary">Create Contract</a>
                @endcan
            @endif
            @can('update', $quotation)
                <a href="{{ route('quotations.edit', $quotation) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $quotation)
                <x-confirm-dialog
                    title="Delete this quotation?"
                    :message="\"This will remove {$quotation->quotation_no} and its items. This cannot be undone.\""
                    action="deleteQuotation"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($quotation->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $item->type->label() }}</td>
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
                        <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium text-gray-900">{{ number_format($quotation->subtotal, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="font-medium text-gray-900">-{{ number_format($quotation->discount, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd class="font-medium text-gray-900">+{{ number_format($quotation->tax, 2) }}</dd></div>
                        <div class="flex justify-between border-t border-gray-100 pt-1 text-base"><dt class="font-semibold text-gray-700">Grand Total</dt><dd class="font-semibold text-gray-900">{{ number_format($quotation->grand_total, 2) }}</dd></div>
                    </dl>
                </div>
            </div>

            @if ($quotation->versions->isNotEmpty())
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Revision history</h2>
                    <ul class="space-y-2">
                        @foreach ($quotation->versions as $version)
                            <li class="flex items-center justify-between rounded-lg border border-gray-100 p-3 text-sm">
                                <span class="font-medium text-gray-900">Version {{ $version->version }}</span>
                                <span class="text-gray-500">Grand total {{ number_format($version->snapshot['grand_total'] ?? 0, 2) }} — {{ $version->created_at?->format('d M, Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Status</h2>
                <div class="space-y-2">
                    @can('update', $quotation)
                        @if ($quotation->status->value === 'draft')
                            <button type="button" wire:click="changeStatus('sent')" class="btn-secondary w-full">Mark as Sent</button>
                        @elseif ($quotation->status->value === 'sent')
                            <button type="button" wire:click="changeStatus('viewed')" class="btn-secondary w-full">Mark as Viewed</button>
                            <button type="button" wire:click="changeStatus('negotiation')" class="btn-secondary w-full">Move to Negotiation</button>
                        @elseif (in_array($quotation->status->value, ['viewed', 'negotiation']))
                            <button type="button" wire:click="changeStatus('negotiation')" class="btn-secondary w-full">Move to Negotiation</button>
                        @endif
                    @endcan

                    @can('recordDecision', $quotation)
                        @if (! $quotation->status->isFinal())
                            <button type="button" wire:click="toggleDecisionForm" class="btn-secondary w-full">
                                {{ $showDecisionForm ? 'Cancel' : 'Record Client Decision' }}
                            </button>
                        @endif
                    @endcan
                </div>

                @if ($showDecisionForm)
                    <form wire:submit="recordDecision" class="mt-4 space-y-3 border-t border-gray-100 pt-4">
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
                        <button type="submit" class="btn-primary w-full">Save decision</button>
                    </form>
                @endif
            </div>

            @if ($quotation->terms || $quotation->notes)
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Terms &amp; Notes</h2>
                    @if ($quotation->terms)
                        <p class="text-sm text-gray-500">Terms</p>
                        <p class="mb-3 whitespace-pre-line text-sm text-gray-900">{{ $quotation->terms }}</p>
                    @endif
                    @if ($quotation->notes)
                        <p class="text-sm text-gray-500">Notes</p>
                        <p class="whitespace-pre-line text-sm text-gray-900">{{ $quotation->notes }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
