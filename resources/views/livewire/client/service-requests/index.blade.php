<div>
    <div class="mb-4 flex justify-end">
        <a href="{{ route('client.service-requests.create') }}" wire:navigate class="btn-primary">New Service Request</a>
    </div>

    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">SR No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Complaint</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($serviceRequests as $sr)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $sr->sr_no }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $sr->warranty?->project?->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($sr->complaint, 40) }}</td>
                        <td class="px-4 py-3 text-sm">{{ $sr->status->label() }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('client.service-requests.show', $sr) }}" wire:navigate class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No service requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $serviceRequests->links() }}</div>
</div>
