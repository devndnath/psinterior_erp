<div>
    <x-page-header title="Vendor Invoices" description="Invoices vendors have submitted themselves against their purchase orders, awaiting or past review.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Vendor Invoices' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Invoice no, vendor…" />
        </div>
        <div>
            <x-select label="Vendor" name="vendor" wire:model.live="vendor">
                <option value="">All vendors</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->company_name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($invoices->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No vendor invoices found"
                description="Invoices vendors submit from their portal will appear here for review."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Vendor</th>
                    <th>Purchase Order</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                    <tr wire:key="vendor-invoice-{{ $invoice->id }}">
                        <td class="font-mono">
                            <a href="{{ route('vendor-invoices.show', $invoice) }}" wire:navigate class="text-accent-600 hover:underline">{{ $invoice->vi_no }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $invoice->vendor?->company_name ?? '—' }}</td>
                        <td>
                            @if ($invoice->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $invoice->purchaseOrder) }}" wire:navigate class="hover:underline">{{ $invoice->purchaseOrder->po_no }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($invoice->amount, 2) }}</td>
                        <td><x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" /></td>
                        <td class="text-right">
                            <a href="{{ route('vendor-invoices.show', $invoice) }}" wire:navigate class="text-ink-500 hover:text-ink-700">Review</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
