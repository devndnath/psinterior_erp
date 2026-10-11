<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'HR' => null]" class="mb-4" />

    <div class="panel mb-4 flex flex-wrap items-end justify-between gap-4 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <x-input label="From" name="dateFrom" type="date" wire:model.live="dateFrom" class="w-40" />
            <x-input label="To" name="dateTo" type="date" wire:model.live="dateTo" class="w-40" />
        </div>
        <x-button variant="secondary" wire:click="exportCsv">Export CSV</x-button>
    </div>

    <div class="mb-6 panel p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Pending Leave Approvals</p>
        <p class="mt-1 text-2xl font-semibold text-ink-900">{{ $pendingLeaveApprovals }}</p>
    </div>

    <x-table>
        <thead>
            <tr>
                <th>Employee</th>
                <th class="text-right">Present</th>
                <th class="text-right">Absent</th>
                <th class="text-right">Late</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="font-medium text-ink-900">{{ $row['employee']->name }}</td>
                    <td class="text-right text-success-600">{{ $row['present'] }}</td>
                    <td class="text-right text-danger-600">{{ $row['absent'] }}</td>
                    <td class="text-right text-warning-600">{{ $row['late'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-ink-400">No employees found.</td></tr>
            @endforelse
        </tbody>
    </x-table>
</div>
