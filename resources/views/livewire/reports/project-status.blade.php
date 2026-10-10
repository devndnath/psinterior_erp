<div>
    <x-breadcrumbs :items="['Reports' => route('reports.index'), 'Project Status' => null]" class="mb-4" />

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div class="flex items-end gap-3">
            <div>
                <label class="form-label">Status</label>
                <select wire:model.live="status" class="block w-48 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                    <option value="">All</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="button" wire:click="exportCsv" class="btn-secondary">Export CSV</button>
    </div>

    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Progress</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Contract Value</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Start</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Expected End</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $project->project_code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $project->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $project->client?->name }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :label="$project->status->label()" :color="$project->status->color()" /></td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $project->progress }}%</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($project->contract_value, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $project->start_date?->format('d M, Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $project->expected_end_date?->format('d M, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-sm text-gray-400">No projects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
