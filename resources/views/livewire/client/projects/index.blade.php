<div>
    @if ($projects->isEmpty())
        <x-empty-state
            title="No projects yet"
            description="Your projects will appear here once work begins."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th class="text-right">Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr wire:key="project-{{ $project->id }}">
                        <td class="font-mono">{{ $project->project_code }}</td>
                        <td class="font-medium text-ink-900">{{ $project->name }}</td>
                        <td><x-status-badge :label="$project->status->label()" :color="$project->status->color()" /></td>
                        <td class="text-right">{{ $project->progress }}%</td>
                        <td class="text-right">
                            <a href="{{ route('client.projects.show', $project) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $projects->links() }}</div>
    @endif
</div>
