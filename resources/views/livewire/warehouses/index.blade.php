<div>
    <x-breadcrumbs :items="['Warehouses' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Warehouses</h1>
        @can('create', \App\Models\Warehouse::class)
            <a href="{{ route('warehouses.create') }}" wire:navigate class="btn-primary">+ New Warehouse</a>
        @endcan
    </div>

    <div class="card overflow-hidden">
        @if ($warehouses->isEmpty())
            <x-empty-state
                title="No warehouses found"
                description="Create a warehouse to start tracking stock in it."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Location</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($warehouses as $warehouse)
                        <tr wire:key="warehouse-{{ $warehouse->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('warehouses.show', $warehouse) }}" wire:navigate class="text-brand-600 hover:underline">{{ $warehouse->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $warehouse->type->label() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $warehouse->location ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('warehouses.show', $warehouse) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $warehouse)
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                                @can('delete', $warehouse)
                                    <span class="ml-3 inline-block">
                                        <x-confirm-dialog
                                            title="Delete this warehouse?"
                                            :message='"This will remove {$warehouse->name}. This cannot be undone."'
                                            action="deleteWarehouse"
                                            :params="[$warehouse->id]"
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
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>
</div>
