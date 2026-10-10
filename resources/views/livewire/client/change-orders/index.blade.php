<div>
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">CO No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Cost</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($changeOrders as $co)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $co->co_no }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $co->project?->name }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :label="$co->status->label()" :color="$co->status->color()" /></td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($co->cost, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('client.change-orders.show', $co) }}" wire:navigate class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No change orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $changeOrders->links() }}</div>
</div>
