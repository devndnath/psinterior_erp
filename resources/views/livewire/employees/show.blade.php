<div>
    <x-page-header :title="$employee->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Employees' => route('employees.index'), $employee->employee_id => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$employee->status->label()" :color="$employee->status->color()" />
            @can('create', \App\Models\Attendance::class)
                <x-button variant="secondary" href="{{ route('attendance.create', ['employee' => $employee->id]) }}" wire:navigate>+ Attendance</x-button>
            @endcan
            @can('create', \App\Models\Leave::class)
                <x-button variant="secondary" href="{{ route('leaves.create', ['employee' => $employee->id]) }}" wire:navigate>+ Leave</x-button>
            @endcan
            @can('create', \App\Models\Payroll::class)
                <x-button variant="secondary" href="{{ route('payroll.create', ['employee' => $employee->id]) }}" wire:navigate>+ Payroll</x-button>
            @endcan
            @can('update', $employee)
                <x-button variant="secondary" href="{{ route('employees.edit', $employee) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $employee)
                <x-confirm-dialog
                    title="Delete this employee?"
                    :message='"This will remove {$employee->employee_id} and its attendance/leave/payroll history. This cannot be undone."'
                    action="deleteEmployee"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $employee->employee_id }} — {{ $employee->designation?->name ?? 'No designation' }}, {{ $employee->department?->name ?? 'No department' }}</p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Phone</dt><dd class="font-medium text-ink-900">{{ $employee->phone ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Email</dt><dd class="font-medium text-ink-900">{{ $employee->email ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Joining Date</dt><dd class="font-medium text-ink-900">{{ $employee->joining_date->format('d M, Y') }}</dd></div>
                    <div><dt class="text-ink-500">Employment Type</dt><dd class="font-medium text-ink-900">{{ $employee->employment_type->label() }}</dd></div>
                    <div><dt class="text-ink-500">Salary</dt><dd class="font-medium text-ink-900">{{ number_format($employee->salary, 2) }}</dd></div>
                    <div><dt class="text-ink-500">Emergency Contact</dt><dd class="font-medium text-ink-900">{{ $employee->emergency_contact ?: '—' }}</dd></div>
                </dl>
                @if ($employee->address)
                    <p class="mt-4 whitespace-pre-line border-t border-ink-100 pt-4 text-sm text-ink-700">{{ $employee->address }}</p>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Recent Attendance (last 14 records)</h2>
                @if ($recentAttendance->isEmpty())
                    <p class="text-sm text-ink-500">No attendance recorded yet.</p>
                @else
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentAttendance as $record)
                                <tr>
                                    <td class="text-ink-900">{{ $record->date->format('d M, Y') }}</td>
                                    <td>{{ $record->check_in?->format('H:i') ?? '—' }}</td>
                                    <td>{{ $record->check_out?->format('H:i') ?? '—' }}</td>
                                    <td><x-status-badge :label="$record->status->label()" :color="$record->status->color()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Leave History</h2>
                @if ($leaves->isEmpty())
                    <p class="text-sm text-ink-500">No leave requests yet.</p>
                @else
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Manager</th>
                                <th>HR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaves as $leave)
                                <tr>
                                    <td class="text-ink-900">
                                        <a href="{{ route('leaves.show', $leave) }}" wire:navigate class="text-accent-600 hover:underline">{{ $leave->type->label() }}</a>
                                    </td>
                                    <td>{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                                    <td><x-status-badge :label="$leave->manager_status->label()" :color="$leave->manager_status->color()" /></td>
                                    <td><x-status-badge :label="$leave->hr_status->label()" :color="$leave->hr_status->color()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Payroll History</h2>
                @if ($payrolls->isEmpty())
                    <p class="text-sm text-ink-500">No payroll runs yet.</p>
                @else
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-right">Net Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payrolls as $payroll)
                                <tr>
                                    <td class="text-ink-900">
                                        <a href="{{ route('payroll.show', $payroll) }}" wire:navigate class="text-accent-600 hover:underline">{{ $payroll->month }}</a>
                                    </td>
                                    <td class="text-right font-medium text-ink-900">{{ number_format($payroll->net_salary, 2) }}</td>
                                    <td><x-status-badge :label="$payroll->status->label()" :color="$payroll->status->color()" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">User Account</h2>
                @if ($employee->user)
                    <p class="text-sm text-ink-900">{{ $employee->user->name }}</p>
                    <p class="text-sm text-ink-500">{{ $employee->user->email }}</p>
                @else
                    <p class="text-sm text-ink-500">No linked user account.</p>
                @endif
            </div>
        </div>
    </div>
</div>
