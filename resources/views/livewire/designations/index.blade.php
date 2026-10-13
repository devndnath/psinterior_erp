<div>
    <x-page-header title="Designations" description="Job titles grouped under a department, assigned to employees.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Designations' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Designation::class)
                <x-button href="{{ route('designations.create') }}" wire:navigate>+ New Designation</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($designations->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No designations found"
                description="Create a department first, then add designations under it."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Employees</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($designations as $designation)
                    <tr wire:key="designation-{{ $designation->id }}">
                        <td class="font-medium text-ink-900">{{ $designation->name }}</td>
                        <td>{{ $designation->department?->name ?? '—' }}</td>
                        <td>{{ $designation->employees_count }}</td>
                        <td class="text-right">
                            @can('update', $designation)
                                <a href="{{ route('designations.edit', $designation) }}" wire:navigate class="text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $designation)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this designation?"
                                        message="This will remove this designation. This cannot be undone."
                                        action="deleteDesignation"
                                        :params="[$designation->id]"
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
            {{ $designations->links() }}
        </div>
    @endif
</div>
