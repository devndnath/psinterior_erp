<div>
    <x-breadcrumbs :items="['Employees' => route('employees.index'), $employee->employee_id => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $employee->name }}</h1>
                <x-status-badge :label="$employee->status->label()" :color="$employee->status->color()" />
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ $employee->employee_id }} — {{ $employee->designation?->name ?? 'No designation' }}, {{ $employee->department?->name ?? 'No department' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @can('create', \App\Models\Attendance::class)
                <a href="{{ route('attendance.create', ['employee' => $employee->id]) }}" wire:navigate class="btn-secondary">+ Attendance</a>
            @endcan
            @can('create', \App\Models\Leave::class)
                <a href="{{ route('leaves.create', ['employee' => $employee->id]) }}" wire:navigate class="btn-secondary">+ Leave</a>
            @endcan
            @can('create', \App\Models\Payroll::class)
                <a href="{{ route('payroll.create', ['employee' => $employee->id]) }}" wire:navigate class="btn-secondary">+ Payroll</a>
            @endcan
            @can('update', $employee)
                <a href="{{ route('employees.edit', $employee) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $employee)
                <x-confirm-dialog
                    title="Delete this employee?"
                    :message='"This will remove {$employee->employee_id} and its attendance/leave/payroll history. This cannot be undone."'
                    action="deleteEmployee"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $employee->phone ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $employee->email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Joining Date</dt><dd class="font-medium text-gray-900">{{ $employee->joining_date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Employment Type</dt><dd class="font-medium text-gray-900">{{ $employee->employment_type->label() }}</dd></div>
                    <div><dt class="text-gray-500">Salary</dt><dd class="font-medium text-gray-900">{{ number_format($employee->salary, 2) }}</dd></div>
                    <div><dt class="text-gray-500">Emergency Contact</dt><dd class="font-medium text-gray-900">{{ $employee->emergency_contact ?: '—' }}</dd></div>
                </dl>
                @if ($employee->address)
                    <p class="mt-4 whitespace-pre-line text-sm text-gray-500">{{ $employee->address }}</p>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Recent Attendance (last 14 records)</h2>
                @if ($recentAttendance->isEmpty())
                    <p class="text-sm text-gray-500">No attendance recorded yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Check In</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Check Out</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentAttendance as $record)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $record->date->format('d M, Y') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $record->check_in?->format('H:i') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $record->check_out?->format('H:i') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm"><x-status-badge :label="$record->status->label()" :color="$record->status->color()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Leave History</h2>
                @if ($leaves->isEmpty())
                    <p class="text-sm text-gray-500">No leave requests yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Dates</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Manager</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">HR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($leaves as $leave)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        <a href="{{ route('leaves.show', $leave) }}" wire:navigate class="text-brand-600 hover:underline">{{ $leave->type->label() }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                                    <td class="px-3 py-2 text-sm"><x-status-badge :label="$leave->manager_status->label()" :color="$leave->manager_status->color()" /></td>
                                    <td class="px-3 py-2 text-sm"><x-status-badge :label="$leave->hr_status->label()" :color="$leave->hr_status->color()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Payroll History</h2>
                @if ($payrolls->isEmpty())
                    <p class="text-sm text-gray-500">No payroll runs yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Month</th>
                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Net Salary</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($payrolls as $payroll)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        <a href="{{ route('payroll.show', $payroll) }}" wire:navigate class="text-brand-600 hover:underline">{{ $payroll->month }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payroll->net_salary, 2) }}</td>
                                    <td class="px-3 py-2 text-sm"><x-status-badge :label="$payroll->status->label()" :color="$payroll->status->color()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">User Account</h2>
                @if ($employee->user)
                    <p class="text-sm text-gray-900">{{ $employee->user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $employee->user->email }}</p>
                @else
                    <p class="text-sm text-gray-500">No linked user account.</p>
                @endif
            </div>
        </div>
    </div>
</div>
