<div>
    <x-breadcrumbs :items="['Change Orders' => route('change-orders.index'), $changeOrder->co_no => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $changeOrder->co_no }}</h1>
                <x-status-badge :label="$changeOrder->status->label()" :color="$changeOrder->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">
                @if ($changeOrder->project)
                    <a href="{{ route('projects.show', $changeOrder->project) }}" wire:navigate class="text-brand-600 hover:underline">{{ $changeOrder->project->project_code }}</a>
                    — {{ $changeOrder->project->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $changeOrder)
                <a href="{{ route('change-orders.edit', $changeOrder) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $changeOrder)
                <x-confirm-dialog
                    title="Delete this change order?"
                    :message='"This will remove {$changeOrder->co_no} and its items. This cannot be undone."'
                    action="deleteChangeOrder"
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
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <p class="whitespace-pre-line text-sm text-gray-900">{{ $changeOrder->description ?: 'No description provided.' }}</p>
            </div>

            @if ($changeOrder->items->isNotEmpty())
                <div class="card p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Line items</h2>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Rate</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($changeOrder->items as $item)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $item->description }}</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-500">{{ $item->qty }}</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-500">{{ number_format($item->rate, 2) }}</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Cost</h2>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format($changeOrder->cost, 2) }}</p>
                <p class="mt-1 text-xs text-gray-400">Added to the project's contract value once approved.</p>

                <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm">
                    <div><dt class="text-gray-500">Client decision</dt>
                        <dd class="font-medium text-gray-900">
                            @if ($changeOrder->client_approval_status)
                                <x-status-badge :label="$changeOrder->client_approval_status->label()" :color="$changeOrder->client_approval_status->color()" />
                            @else
                                Pending
                            @endif
                        </dd>
                    </div>
                    @if ($changeOrder->approved_at)
                        <div><dt class="text-gray-500">Approved at</dt><dd class="font-medium text-gray-900">{{ $changeOrder->approved_at->format('d M, Y H:i') }}</dd></div>
                    @endif
                </dl>

                @can('recordDecision', $changeOrder)
                    @if ($changeOrder->status->value === 'pending')
                        <button type="button" wire:click="toggleDecisionForm" class="btn-secondary mt-4 w-full">
                            {{ $showDecisionForm ? 'Cancel' : 'Record Client Decision' }}
                        </button>

                        @if ($showDecisionForm)
                            <form wire:submit="recordDecision" class="mt-4 space-y-3">
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
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>
