<div>
    <x-breadcrumbs :items="['Leaves' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Leaves</h1>
        @can('create', \App\Models\Leave::class)
            <a href="{{ route('leaves.create') }}" wire:navigate class="btn-primary">+ New Leave Request</a>
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
            <label class="form-label">Type</label>
            <select wire:model.live="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any type</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($leaves->isEmpty())
            <x-empty-state
                title="No leave requests found"
                description="Submit a leave request to see it listed here."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Manager</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">HR</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($leaves as $leave)
                        <tr wire:key="leave-{{ $leave->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $leave->employee?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $leave->type->label() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-sm"><x-status-badge :label="$leave->manager_status->label()" :color="$leave->manager_status->color()" /></td>
                            <td class="px-4 py-3 text-sm"><x-status-badge :label="$leave->hr_status->label()" :color="$leave->hr_status->color()" /></td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('leaves.show', $leave) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</div>
