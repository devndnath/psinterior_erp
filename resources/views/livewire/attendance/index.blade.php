<div>
    <x-breadcrumbs :items="['Attendance' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Attendance</h1>
        @can('create', \App\Models\Attendance::class)
            <a href="{{ route('attendance.create') }}" wire:navigate class="btn-primary">+ Record Attendance</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <label class="form-label">Employee</label>
            <select wire:model.live="employee" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any employee</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">From</label>
            <input wire:model.live="from" type="date" class="block rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">To</label>
            <input wire:model.live="to" type="date" class="block rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
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
        @if ($records->isEmpty())
            <x-empty-state
                title="No attendance records found"
                description="Record attendance for an employee to see it listed here."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Check In</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Check Out</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($records as $record)
                        <tr wire:key="att-{{ $record->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $record->date->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if ($record->employee)
                                    <a href="{{ route('employees.show', $record->employee) }}" wire:navigate class="hover:underline">{{ $record->employee->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $record->check_in?->format('H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $record->check_out?->format('H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$record->status->label()" :color="$record->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                @can('update', $record)
                                    <a href="{{ route('attendance.edit', $record) }}" wire:navigate class="text-gray-500 hover:text-gray-700">Edit</a>
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
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
