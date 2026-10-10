<div>
    <x-breadcrumbs :items="['Vendors' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Vendors</h1>
        @can('create', \App\Models\Vendor::class)
            <a href="{{ route('vendors.create') }}" wire:navigate class="btn-primary">+ New Vendor</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Company name or code…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($vendors->isEmpty())
            <x-empty-state
                title="No vendors found"
                description="Add a vendor to start the procurement pipeline — purchase requests, quotations and purchase orders."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Company</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Contact</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($vendors as $vendor)
                        <tr wire:key="vendor-{{ $vendor->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('vendors.show', $vendor) }}" wire:navigate class="text-brand-600 hover:underline">{{ $vendor->vendor_code }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $vendor->company_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $vendor->category ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $vendor->contact_person ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('vendors.show', $vendor) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $vendor)
                                    <a href="{{ route('vendors.edit', $vendor) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $vendor)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this vendor?"
                                            :message='"This will remove {$vendor->company_name} from the directory. This cannot be undone."'
                                            action="deleteVendor"
                                            :params="[$vendor->id]"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="text-red-500 hover:text-red-700">Delete</button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    </span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</div>
