<div>
    <x-page-header title="Attendance" description="Record and review daily check-in/check-out for each employee.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Attendance' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Attendance::class)
                <x-button href="{{ route('attendance.create') }}" wire:navigate>+ Record Attendance</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Employee" name="employee" wire:model.live="employee">
                <option value="">Any employee</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-input label="From" name="from" type="date" wire:model.live="from" />
        </div>
        <div>
            <x-input label="To" name="to" type="date" wire:model.live="to" />
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

    @if ($records->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No attendance records found"
                description="Record attendance for an employee to see it listed here."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr wire:key="att-{{ $record->id }}">
                        <td class="text-ink-900">{{ $record->date->format('d M, Y') }}</td>
                        <td>
                            @if ($record->employee)
                                <a href="{{ route('employees.show', $record->employee) }}" wire:navigate class="hover:underline">{{ $record->employee->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $record->check_in?->format('H:i') ?? '—' }}</td>
                        <td>{{ $record->check_out?->format('H:i') ?? '—' }}</td>
                        <td>
                            <x-status-badge :label="$record->status->label()" :color="$record->status->color()" />
                        </td>
                        <td class="text-right">
                            @can('update', $record)
                                <a href="{{ route('attendance.edit', $record) }}" wire:navigate class="text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $record)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this attendance record?"
                                        message="This cannot be undone."
                                        action="deleteAttendance"
                                        :params="[$record->id]"
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
            {{ $records->links() }}
        </div>
    @endif
</div>
