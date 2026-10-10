<div>
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Design No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($designs as $design)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $design->design_no }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $design->title }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $design->project?->name }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :label="$design->status->label()" :color="$design->status->color()" /></td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('client.designs.show', $design) }}" wire:navigate class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No designs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $designs->links() }}</div>
</div>
