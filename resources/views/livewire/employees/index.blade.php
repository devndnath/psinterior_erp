<div>
    <x-page-header title="Employees" description="Track staff records, departments and designations.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Employees' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Employee::class)
                <x-button href="{{ route('employees.create') }}" wire:navigate>+ New Employee</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name or employee no…" />
        </div>
        <div>
            <x-select label="Department" name="department" wire:model.live="department">
                <option value="">Any department</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($employees->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No employees found"
                description="Add an employee to start tracking attendance, leave and payroll."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Employee #</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr wire:key="emp-{{ $employee->id }}">
                        <td class="font-mono">
                            <a href="{{ route('employees.show', $employee) }}" wire:navigate class="text-accent-600 hover:underline">{{ $employee->employee_id }}</a>
                        </td>
                        <td class="text-ink-900">{{ $employee->name }}</td>
                        <td>{{ $employee->department?->name ?? '—' }}</td>
                        <td>{{ $employee->designation?->name ?? '—' }}</td>
                        <td>
                            <x-status-badge :label="$employee->status->label()" :color="$employee->status->color()" />
                        </td>
                        <td class="text-right">
                            <a href="{{ route('employees.show', $employee) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $employee)
                                <a href="{{ route('employees.edit', $employee) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $employee)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this employee?"
                                        :message='"This will remove {$employee->employee_id} and its attendance/leave/payroll history. This cannot be undone."'
                                        action="deleteEmployee"
                                        :params="[$employee->id]"
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
            {{ $employees->links() }}
        </div>
    @endif
</div>
