<div>
    <x-breadcrumbs :items="['Units' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Units</h1>
        @can('create', \App\Models\Unit::class)
            <a href="{{ route('units.create') }}" wire:navigate class="btn-primary">+ New Unit</a>
        @endcan
    </div>

    <div class="card overflow-hidden">
        @if ($units->isEmpty())
            <x-empty-state
                title="No units found"
                description="Create a unit of measure to assign to products."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Short Code</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($units as $unit)
                        <tr wire:key="unit-{{ $unit->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $unit->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $unit->short_code }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                @can('update', $unit)
                                    <a href="{{ route('units.edit', $unit) }}" wire:navigate class="text-gray-500 hover:text-gray-700">Edit</a>
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
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
