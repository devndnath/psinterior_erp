<div>
    <x-breadcrumbs :items="['BOQs' => route('boqs.index'), $boq->boq_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $boq->boq_no }}</h1>
                <span class="text-sm text-gray-400">v{{ $boq->version }}</span>
                <x-status-badge :label="$boq->status->label()" :color="$boq->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($boq->project)
                    <a href="{{ route('projects.show', $boq->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $boq->project->project_code }}</a>
                    — {{ $boq->project->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="downloadPdf" class="btn-secondary">Download PDF</button>
            @can('create', \App\Models\Quotation::class)
                <a href="{{ route('quotations.create', ['project' => $boq->project_id, 'boq' => $boq->id]) }}" wire:navigate class="btn-secondary">Create Quotation</a>
            @endcan
            @can('update', $boq)
                @if ($boq->status->value !== 'active')
                    <button type="button" wire:click="activate" wire:confirm="Activate this BOQ? Any other active BOQ for this project will be superseded." class="btn-secondary">Activate</button>
                @endif
                <a href="{{ route('boqs.edit', $boq) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('create', \App\Models\Boq::class)
                <button type="button" wire:click="createNewVersion" class="btn-secondary">New Version</button>
            @endcan
            @can('delete', $boq)
                <x-confirm-dialog
                    title="Delete this BOQ?"
                    :message='"This will remove {$boq->boq_no} and its items. This cannot be undone."'
                    action="deleteBoq"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Item</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Unit</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($boq->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $item->item }}
                            @if ($item->description)
                                <p class="text-xs text-gray-500">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->category ?: '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $item->qty }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->unit ?: '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t border-gray-100 px-4 py-4">
            <dl class="ml-auto max-w-xs space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-medium text-gray-900">{{ number_format($boq->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="font-medium text-gray-900">-{{ number_format($boq->discount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd class="font-medium text-gray-900">+{{ number_format($boq->tax, 2) }}</dd></div>
                <div class="flex justify-between border-t border-gray-100 pt-1 text-base"><dt class="font-semibold text-gray-700">Grand Total</dt><dd class="font-semibold text-gray-900">{{ number_format($boq->grand_total, 2) }}</dd></div>
            </dl>
        </div>
    </div>
</div>
