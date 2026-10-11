<div>
    <x-page-header title="Leaves" description="Review and track employee leave requests through manager and HR approval.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leaves' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Leave::class)
                <x-button href="{{ route('leaves.create') }}" wire:navigate>+ New Leave Request</x-button>
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
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($leaves->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No leave requests found"
                description="Submit a leave request to see it listed here."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Dates</th>
                    <th>Manager</th>
                    <th>HR</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    <tr wire:key="leave-{{ $leave->id }}">
                        <td class="text-ink-900">{{ $leave->employee?->name ?? '—' }}</td>
                        <td>{{ $leave->type->label() }}</td>
                        <td>{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                        <td><x-status-badge :label="$leave->manager_status->label()" :color="$leave->manager_status->color()" /></td>
                        <td><x-status-badge :label="$leave->hr_status->label()" :color="$leave->hr_status->color()" /></td>
                        <td class="text-right">
                            <a href="{{ route('leaves.show', $leave) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $leaves->links() }}
        </div>
    @endif
</div>
