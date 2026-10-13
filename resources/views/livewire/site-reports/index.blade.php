<div>
    <x-page-header title="Site Reports" description="Daily progress logs filed from the field — manpower, weather, work done, and issues.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Site Reports' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\SiteReport::class)
                <x-button href="{{ route('site-reports.create') }}" wire:navigate>+ New Site Report</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Work summary, project…" />
        </div>
        <div>
            <x-select label="Project" name="project" wire:model.live="project">
                <option value="">All projects</option>
                @foreach ($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->project_code }} — {{ $proj->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($reports->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No site reports found"
                description="File a daily site report to start tracking progress for a project."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th class="th-sortable" wire:click="sortBy('report_date')">Date</th>
                    <th>Project</th>
                    <th>Weather</th>
                    <th>Manpower</th>
                    <th>Reported by</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr wire:key="report-{{ $report->id }}">
                        <td class="font-mono">
                            <a href="{{ route('site-reports.show', $report) }}" wire:navigate class="text-accent-600 hover:underline">{{ $report->report_date?->format('d M, Y') }}</a>
                        </td>
                        <td class="font-medium text-ink-900">
                            @if ($report->project)
                                <a href="{{ route('projects.show', $report->project) }}" wire:navigate class="hover:underline">{{ $report->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $report->weather ?: '—' }}</td>
                        <td>{{ $report->manpower_count ?? '—' }}</td>
                        <td>{{ $report->reporter?->name ?? '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('site-reports.show', $report) }}" wire:navigate class="text-ink-500 hover:text-ink-700">View</a>
                            @can('update', $report)
                                <a href="{{ route('site-reports.edit', $report) }}" wire:navigate class="ml-3 text-ink-500 hover:text-ink-700">Edit</a>
                            @endcan
                            @can('delete', $report)
                                <span class="ml-3 inline-block">
                                    <x-confirm-dialog
                                        title="Delete this site report?"
                                        message="This will remove this site report. This cannot be undone."
                                        action="deleteSiteReport"
                                        :params="[$report->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="text-danger-600 hover:text-danger-700">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    @endif
</div>
