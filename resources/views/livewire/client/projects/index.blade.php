<div>
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $project->project_code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $project->name }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :label="$project->status->label()" :color="$project->status->color()" /></td>
                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $project->progress }}%</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('client.projects.show', $project) }}" wire:navigate class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
</div>
