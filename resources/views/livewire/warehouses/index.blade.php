<div>
    <x-page-header title="Warehouses" description="Storage locations tracked for stock quantities and movements.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Warehouses' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Warehouse::class)
                <x-button href="{{ route('warehouses.create') }}" wire:navigate>+ New Warehouse</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($warehouses->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No warehouses found"
                description="Create a warehouse to start tracking stock in it."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($warehouses as $warehouse)
                    <tr wire:key="warehouse-{{ $warehouse->id }}">
                        <td class="font-medium text-ink-900">
                            <a href="{{ route('warehouses.show', $warehouse) }}" wire:navigate class="text-accent-600 hover:underline">{{ $warehouse->name }}</a>
                        </td>
                        <td>{{ $warehouse->type->label() }}</td>
                        <td>{{ $warehouse->location ?: '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('warehouses.show', $warehouse) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $warehouse)
                                <a href="{{ route('warehouses.edit', $warehouse) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
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
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $warehouses->links() }}
        </div>
    @endif
</div>
