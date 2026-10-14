<div>
    <x-page-header title="Vendors" description="Manage the vendor directory used across purchase requests, quotations and purchase orders.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Vendors' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Vendor::class)
                <x-button href="{{ route('vendors.create') }}" wire:navigate>+ New Vendor</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Company name or code…" />
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($vendors->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No vendors found"
                description="Add a vendor to start the procurement pipeline — purchase requests, quotations and purchase orders."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Company</th>
                    <th>Category</th>
                    <th>Contact</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vendors as $vendor)
                    <tr wire:key="vendor-{{ $vendor->id }}">
                        <td class="font-mono">
                            <a href="{{ route('vendors.show', $vendor) }}" wire:navigate class="text-accent-600 hover:underline">{{ $vendor->vendor_code }}</a>
                        </td>
                        <td class="font-medium text-ink-900">{{ $vendor->company_name }}</td>
                        <td>{{ $vendor->category ?: '—' }}</td>
                        <td>{{ $vendor->contact_person ?: '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('vendors.show', $vendor) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $vendor)
                                <a href="{{ route('vendors.edit', $vendor) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $vendor)
                                                                    <x-confirm-dialog
                                        title="Delete this vendor?"
                                        :message='"This will remove {$vendor->company_name} from the directory. This cannot be undone."'
                                        action="deleteVendor"
                                        :params="[$vendor->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="dropdown-item-danger">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                            @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $vendors->links() }}
        </div>
    @endif
</div>
