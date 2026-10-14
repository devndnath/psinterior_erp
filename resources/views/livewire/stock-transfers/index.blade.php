<div>
    <x-page-header title="Stock Transfers" description="Move stock between warehouses and track delivery.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Stock Transfers' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\StockTransfer::class)
                <x-button href="{{ route('stock-transfers.create') }}" wire:navigate>+ New Transfer</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Transfer no…" />
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($transfers->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No stock transfers found"
                description="Create a transfer to move stock between warehouses."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Transfer #</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfers as $transfer)
                    <tr wire:key="transfer-{{ $transfer->id }}">
                        <td class="font-mono">
                            <a href="{{ route('stock-transfers.show', $transfer) }}" wire:navigate class="text-accent-600 hover:underline">{{ $transfer->transfer_no }}</a>
                        </td>
                        <td class="text-ink-500">{{ $transfer->fromWarehouse?->name }}</td>
                        <td class="text-ink-500">{{ $transfer->toWarehouse?->name }}</td>
                        <td>
                            <x-status-badge :label="$transfer->status->label()" :color="$transfer->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('stock-transfers.show', $transfer) }}" wire:navigate class="dropdown-item">View</a>
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $transfers->links() }}
        </div>
    @endif
</div>
