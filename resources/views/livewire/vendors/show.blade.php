<div>
    <x-page-header :title="$vendor->company_name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Vendors' => route('vendors.index'), $vendor->vendor_code => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('update', $vendor)
                <x-button variant="secondary" href="{{ route('vendors.edit', $vendor) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $vendor)
                <x-confirm-dialog
                    title="Delete this vendor?"
                    :message='"This will remove {$vendor->company_name} from the directory. This cannot be undone."'
                    action="deleteVendor"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $vendor->vendor_code }} @if ($vendor->category) &middot; {{ $vendor->category }} @endif</p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Contact Person</dt><dd class="font-medium text-ink-900">{{ $vendor->contact_person ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Phone</dt><dd class="font-medium text-ink-900">{{ $vendor->phone ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Email</dt><dd class="font-medium text-ink-900">{{ $vendor->email ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Tax Info</dt><dd class="font-medium text-ink-900">{{ $vendor->tax_info ?: '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Address</dt><dd class="font-medium text-ink-900">{{ $vendor->address ?: '—' }}</dd></div>
                </dl>
                @if ($vendor->notes)
                    <p class="mt-4 whitespace-pre-line border-t border-ink-100 pt-4 text-sm text-ink-900">{{ $vendor->notes }}</p>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Linked Products</h2>
                @if ($products->isEmpty())
                    <p class="text-sm text-ink-500">No products linked to this vendor yet.</p>
                @else
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Vendor Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td class="text-ink-900">{{ $product->name }} <span class="text-ink-400">({{ $product->sku }})</span></td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($product->pivot->vendor_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Recent Purchase Orders</h2>
                @if ($purchaseOrders->isEmpty())
                    <p class="text-sm text-ink-500">No purchase orders raised against this vendor yet.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($purchaseOrders as $po)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $po->po_no }}</a>
                                <div class="flex items-center gap-3">
                                    <span class="text-ink-500">{{ number_format($po->total, 2) }}</span>
                                    <x-status-badge :label="$po->status->label()" :color="$po->status->color()" />
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
