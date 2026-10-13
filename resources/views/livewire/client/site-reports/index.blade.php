<div>
    @if ($reports->isEmpty())
        <x-empty-state
            title="No site reports yet"
            description="Daily progress updates from the site will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Weather</th>
                    <th>Manpower</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr wire:key="report-{{ $report->id }}">
                        <td class="font-medium text-ink-900">{{ $report->report_date?->format('d M, Y') }}</td>
                        <td>{{ $report->project?->name }}</td>
                        <td>{{ $report->weather ?: '—' }}</td>
                        <td>{{ $report->manpower_count ?? '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('client.site-reports.show', $report) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $reports->links() }}</div>
    @endif
</div>
