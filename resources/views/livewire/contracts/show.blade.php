<div>
    <x-page-header :title="$contract->contract_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Contracts' => route('contracts.index'), $contract->contract_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$contract->status->label()" :color="$contract->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            @can('update', $contract)
                @if ($contract->status->value !== 'active')
                    <button type="button" wire:click="activate" wire:confirm="Activate this contract? This will update the project's contract value." class="btn-secondary">Activate</button>
                @endif
                <x-button variant="secondary" href="{{ route('contracts.edit', $contract) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $contract)
                <x-confirm-dialog
                    title="Delete this contract?"
                    :message='"This will remove {$contract->contract_no} and its items. This cannot be undone."'
                    action="deleteContract"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $contract->client?->name }}
        @if ($contract->project)
            — <a href="{{ route('projects.show', $contract->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $contract->project->project_code }}</a>
        @endif
        @if ($contract->quotation)
            — from <a href="{{ route('quotations.show', $contract->quotation) }}" wire:navigate class="hover:underline">{{ $contract->quotation->quotation_no }}</a>
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Line items</h2>
                @if ($contract->items->isEmpty())
                    <p class="text-sm text-ink-500">No line items recorded.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($contract->items as $item)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <span class="text-ink-900">{{ $item->description }}</span>
                                <span class="font-medium text-ink-900">{{ number_format($item->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-4 flex justify-between border-t border-ink-100 pt-4 text-base">
                    <span class="font-semibold text-ink-700">Contract Amount</span>
                    <span class="font-semibold text-ink-900">{{ number_format($contract->amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-ink-500">Start date</dt><dd class="font-medium text-ink-900">{{ $contract->start_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">End date</dt><dd class="font-medium text-ink-900">{{ $contract->end_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Payment terms</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $contract->payment_terms ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Warranty terms</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $contract->warranty_terms ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Terms</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $contract->terms ?: '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
