<div>
    <x-page-header title="My Leaves" description="Apply for leave and track your own requests through manager and HR approval.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['My Leaves' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-button href="{{ route('my-leaves.create') }}" wire:navigate>+ Apply for Leave</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    @if ($leaves->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No leave requests yet"
                description="Apply for leave and it will show up here with its approval status."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Dates</th>
                    <th>Reason</th>
                    <th>Manager</th>
                    <th>HR</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    <tr wire:key="my-leave-{{ $leave->id }}">
                        <td class="text-ink-900">{{ $leave->type->label() }}</td>
                        <td>{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                        <td class="max-w-xs truncate text-ink-500">{{ $leave->reason ?: '—' }}</td>
                        <td><x-status-badge :label="$leave->manager_status->label()" :color="$leave->manager_status->color()" /></td>
                        <td><x-status-badge :label="$leave->hr_status->label()" :color="$leave->hr_status->color()" /></td>
                        <td class="text-right">
                            @if ($leave->manager_status->value === 'pending' && $leave->hr_status->value === 'pending')
                                <x-confirm-dialog
                                    title="Cancel this leave request?"
                                    message="This withdraws your request before it has been reviewed."
                                    action="cancel"
                                    :params="[$leave->id]"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="text-sm font-medium text-danger-600 hover:underline">Cancel</button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                            @endif
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
