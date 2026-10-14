<div>
    <x-page-header title="Departments" description="The department list used by Employee records and the designations under each one.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Departments' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Department::class)
                <x-button href="{{ route('departments.create') }}" wire:navigate>+ New Department</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if ($departments->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No departments found"
                description="Create a department before assigning employees or designations to it."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Designations</th>
                    <th>Employees</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departments as $department)
                    <tr wire:key="department-{{ $department->id }}">
                        <td class="font-medium text-ink-900">{{ $department->name }}</td>
                        <td>{{ $department->code ?: '—' }}</td>
                        <td>{{ $department->designations_count }}</td>
                        <td>{{ $department->employees_count }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            @can('update', $department)
                                <a href="{{ route('departments.edit', $department) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $department)
                                                                    <x-confirm-dialog
                                        title="Delete this department?"
                                        message="This will remove this department. This cannot be undone."
                                        action="deleteDepartment"
                                        :params="[$department->id]"
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
            {{ $departments->links() }}
        </div>
    @endif
</div>
