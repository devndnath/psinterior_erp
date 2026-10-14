<div>
    <x-page-header :title="$changeOrder->co_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Change Orders' => route('change-orders.index'), $changeOrder->co_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$changeOrder->status->label()" :color="$changeOrder->status->color()" />
            @can('update', $changeOrder)
                <x-button variant="secondary" href="{{ route('change-orders.edit', $changeOrder) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $changeOrder)
                <x-confirm-dialog
                    title="Delete this change order?"
                    :message='"This will remove {$changeOrder->co_no} and its items. This cannot be undone."'
                    action="deleteChangeOrder"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        @if ($changeOrder->project)
            <a href="{{ route('projects.show', $changeOrder->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $changeOrder->project->project_code }}</a>
            — {{ $changeOrder->project->name }}
        @endif
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <p class="whitespace-pre-line text-sm text-ink-900">{{ $changeOrder->description ?: 'No description provided.' }}</p>
            </div>

            @if ($changeOrder->items->isNotEmpty())
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Line items</h2>
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
                            @foreach ($changeOrder->items as $item)
                                <tr>
                                    <td class="text-ink-900">{{ $item->description }}</td>
                                    <td class="text-right">{{ $item->qty }}</td>
                                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Cost</h2>
                <p class="text-3xl font-semibold text-ink-900">{{ number_format($changeOrder->cost, 2) }}</p>
                <p class="mt-1 text-xs text-ink-400">Added to the project's contract value once approved.</p>

                <dl class="mt-4 space-y-2 border-t border-ink-100 pt-4 text-sm">
                    <div><dt class="text-ink-500">Client decision</dt>
                        <dd class="font-medium text-ink-900">
                            @if ($changeOrder->client_approval_status)
                                <x-status-badge :label="$changeOrder->client_approval_status->label()" :color="$changeOrder->client_approval_status->color()" />
                            @else
                                Pending
                            @endif
                        </dd>
                    </div>
                    @if ($changeOrder->approved_at)
                        <div><dt class="text-ink-500">Approved at</dt><dd class="font-medium text-ink-900">{{ $changeOrder->approved_at->format('d M, Y H:i') }}</dd></div>
                    @endif
                </dl>

                @can('recordDecision', $changeOrder)
                    @if ($changeOrder->status->value === 'pending')
                        <x-button variant="secondary" wire:click="toggleDecisionForm" class="mt-4 w-full">
                            {{ $showDecisionForm ? 'Cancel' : 'Record Client Decision' }}
                        </x-button>

                        @if ($showDecisionForm)
                            <form wire:submit="recordDecision" class="mt-4 space-y-3">
                                <x-select label="Decision *" name="decision" wire:model="decision">
                                    <option value="">Select…</option>
                                    @foreach ($decisionOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-select>
                                <x-button type="submit" class="w-full">Save decision</x-button>
                            </form>
                        @endif
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>
