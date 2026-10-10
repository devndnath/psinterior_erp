<div>
    <x-breadcrumbs :items="['Employees' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Employees</h1>
        @can('create', \App\Models\Employee::class)
            <a href="{{ route('employees.create') }}" wire:navigate class="btn-primary">+ New Employee</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="form-label">Search</label>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Name or employee no…" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Department</label>
            <select wire:model.live="department" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any department</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($employees->isEmpty())
            <x-empty-state
                title="No employees found"
                description="Add an employee to start tracking attendance, leave and payroll."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Employee #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Designation</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($employees as $employee)
                        <tr wire:key="emp-{{ $employee->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-gray-500">
                                <a href="{{ route('employees.show', $employee) }}" wire:navigate class="text-brand-600 hover:underline">{{ $employee->employee_id }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $employee->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $employee->department?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $employee->designation?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$employee->status->label()" :color="$employee->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('employees.show', $employee) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $employee)
                                    <a href="{{ route('employees.edit', $employee) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
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
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
