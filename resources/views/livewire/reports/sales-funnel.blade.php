<div>
    {{-- Phase 16: see reports/hr.blade.php — this report had no heading
         of its own and relied on layouts.admin's old fallback <h1>. --}}
    <x-page-header title="Sales Funnel Report">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Sales Funnel' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end justify-between gap-4 p-4 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <x-input label="From" name="dateFrom" type="date" wire:model.live="dateFrom" class="w-40" />
            <x-input label="To" name="dateTo" type="date" wire:model.live="dateTo" class="w-40" />
        </div>
        <x-button variant="secondary" class="no-print" wire:click="exportCsv">Export CSV</x-button>
        <x-button variant="secondary" class="no-print" onclick="window.print()">Print</x-button>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @foreach ($byStatus as $row)
            <div class="panel p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $row['label'] }}</p>
                <p class="mt-1 text-2xl font-semibold text-ink-900">{{ $row['count'] }}</p>
            </div>
        @endforeach
    </div>

    <x-table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="text-right">Total Leads</th>
                <th class="text-right">Won</th>
                <th class="text-right">Conversion Rate</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bySource as $row)
                <tr>
                    <td class="font-medium text-ink-900">{{ $row['source'] }}</td>
                    <td class="text-right text-ink-500">{{ $row['total'] }}</td>
                    <td class="text-right text-ink-500">{{ $row['won'] }}</td>
                    <td class="text-right text-ink-500">{{ $row['conversion_rate'] !== null ? $row['conversion_rate'].'%' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-ink-400">No lead sources found.</td></tr>
            @endforelse
        </tbody>
    </x-table>
</div>
