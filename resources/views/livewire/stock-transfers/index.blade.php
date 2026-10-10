<div>
    <x-breadcrumbs :items="['Stock Transfers' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Stock Transfers</h1>
        @can('create', \App\Models\StockTransfer::class)
            <a href="{{ route('stock-transfers.create') }}" wire:navigate class="btn-primary">+ New Transfer</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Transfer no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($transfers->isEmpty())
            <x-empty-state
                title="No stock transfers found"
                description="Create a transfer to move stock between warehouses."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Transfer #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">From</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">To</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($transfers as $transfer)
                        <tr wire:key="transfer-{{ $transfer->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('stock-transfers.show', $transfer) }}" wire:navigate class="text-brand-600 hover:underline">{{ $transfer->transfer_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $transfer->fromWarehouse?->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $transfer->toWarehouse?->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$transfer->status->label()" :color="$transfer->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('stock-transfers.show', $transfer) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>
