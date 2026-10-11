<div>
    <x-page-header title="Units" description="Units of measure assignable to products.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Units' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Unit::class)
                <x-button href="{{ route('units.create') }}" wire:navigate>+ New Unit</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($units->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No units found"
                description="Create a unit of measure to assign to products."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Short Code</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($units as $unit)
                    <tr wire:key="unit-{{ $unit->id }}">
                        <td class="font-medium text-ink-900">{{ $unit->name }}</td>
                        <td>{{ $unit->short_code }}</td>
                        <td class="text-right">
                            @can('update', $unit)
                                <a href="{{ route('units.edit', $unit) }}" wire:navigate class="text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $unit)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this unit?"
                                        :message='"This will remove {$unit->name}. This cannot be undone."'
                                        action="deleteUnit"
                                        :params="[$unit->id]"
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
            {{ $units->links() }}
        </div>
    @endif
</div>
