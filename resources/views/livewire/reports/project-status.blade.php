<div>
    {{-- Phase 16: see reports/hr.blade.php — this report had no heading
         of its own and relied on layouts.admin's old fallback <h1>. --}}
    <x-page-header title="Project Status Report">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Project Status' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end justify-between gap-4 p-4 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <x-select label="Status" name="status" wire:model.live="status" class="w-48">
                <option value="">All</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" class="no-print" wire:click="exportCsv">Export CSV</x-button>
        <x-button variant="secondary" class="no-print" onclick="window.print()">Print</x-button>
    </div>

    <x-table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Client</th>
                <th>Status</th>
                <th class="text-right">Progress</th>
                <th class="text-right">Contract Value</th>
                <th>Start</th>
                <th>Expected End</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td class="text-ink-500">{{ $project->project_code }}</td>
                    <td class="font-medium text-ink-900">{{ $project->name }}</td>
                    <td class="text-ink-500">{{ $project->client?->name }}</td>
                    <td><x-status-badge :label="$project->status->label()" :color="$project->status->color()" /></td>
                    <td class="text-right text-ink-500">{{ $project->progress }}%</td>
                    <td class="text-right text-ink-500">{{ number_format($project->contract_value, 2) }}</td>
                    <td class="text-ink-500">{{ $project->start_date?->format('d M, Y') }}</td>
                    <td class="text-ink-500">{{ $project->expected_end_date?->format('d M, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-6 text-center text-sm text-ink-400">No projects found.</td></tr>
            @endforelse
        </tbody>
    </x-table>
</div>
