<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Sales Funnel' => null]" class="mb-4" />

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

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @foreach ($byStatus as $row)
            <div class="card p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $row['label'] }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $row['count'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Source</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total Leads</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Won</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Conversion Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($bySource as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['source'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $row['total'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $row['won'] }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $row['conversion_rate'] !== null ? $row['conversion_rate'].'%' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No lead sources found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
