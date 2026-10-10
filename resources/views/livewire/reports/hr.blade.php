<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'HR' => null]" class="mb-4" />

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div class="flex items-end gap-3">
            <div>
                <label class="form-label">From</label>
                <input type="date" wire:model.live="dateFrom" class="block w-40 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" wire:model.live="dateTo" class="block w-40 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            </div>
        </div>
        <button type="button" wire:click="exportCsv" class="btn-secondary">Export CSV</button>
    </div>

    <div class="mb-6 card p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pending Leave Approvals</p>
        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $pendingLeaveApprovals }}</p>
    </div>

    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Employee</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Present</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Absent</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Late</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['employee']->name }}</td>
                        <td class="px-4 py-3 text-right text-sm text-green-600">{{ $row['present'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-red-600">{{ $row['absent'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-yellow-600">{{ $row['late'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
