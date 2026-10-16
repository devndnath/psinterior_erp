<div>
    <x-page-header :title="$boq->boq_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['BOQs' => route('boqs.index'), $boq->boq_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <span class="text-sm text-ink-400">v{{ $boq->version }}</span>
            <x-status-badge :label="$boq->status->label()" :color="$boq->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            <x-button variant="secondary" href="{{ route('boqs.pdf', [$boq, 'print' => 1]) }}" target="_blank">Print</x-button>
            @can('create', \App\Models\Quotation::class)
                <x-button variant="secondary" href="{{ route('quotations.create', ['project' => $boq->project_id, 'boq' => $boq->id]) }}" wire:navigate>Create Quotation</x-button>
            @endcan
            @can('update', $boq)
                @if ($boq->status->value !== 'active')
                    <button type="button" wire:click="activate" wire:confirm="Activate this BOQ? Any other active BOQ for this project will be superseded." class="btn-secondary">Activate</button>
                @endif
                <x-button variant="secondary" href="{{ route('boqs.edit', $boq) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('create', \App\Models\Boq::class)
                <x-button variant="secondary" wire:click="createNewVersion">New Version</x-button>
            @endcan
            @can('delete', $boq)
                <x-confirm-dialog
                    title="Delete this BOQ?"
                    :message='"This will remove {$boq->boq_no} and its items. This cannot be undone."'
                    action="deleteBoq"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($boq->project)
            <a href="{{ route('projects.show', $boq->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $boq->project->project_code }}</a>
            — {{ $boq->project->name }}
        @endif
    </p>

    {{-- Line items + totals summary: kept as its existing table-like
         structure (reskinned to table-base/panel tokens) rather than
         forced into <x-table>, since the totals <dl> block below the
         rows doesn't fit that component's thead/tbody-only slot
         contract — same judgment call Phase 15b made for Quotations. --}}
    <div class="panel overflow-hidden">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th class="text-right">Qty</th>
                    <th>Unit</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boq->items as $item)
                    <tr>
                        <td class="font-medium text-ink-900">
                            {{ $item->item }}
                            @if ($item->description)
                                <p class="text-xs text-ink-500">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td>{{ $item->category ?: '—' }}</td>
                        <td class="text-right">{{ $item->qty }}</td>
                        <td>{{ $item->unit ?: '—' }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t border-ink-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-ink-500">Subtotal</dt><dd class="font-medium text-ink-900">{{ number_format($boq->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-500">Discount</dt><dd class="font-medium text-ink-900">-{{ number_format($boq->discount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-500">Tax</dt><dd class="font-medium text-ink-900">+{{ number_format($boq->tax, 2) }}</dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-1 text-base"><dt class="font-semibold text-ink-700">Grand Total</dt><dd class="font-semibold text-ink-900">{{ number_format($boq->grand_total, 2) }}</dd></div>
            </dl>
        </div>
    </div>
</div>
