<div>
    <div class="mb-4 flex justify-end">
        <x-button href="{{ route('client.service-requests.create') }}" wire:navigate>New Service Request</x-button>
    </div>

    @if ($serviceRequests->isEmpty())
        <x-empty-state
            title="No service requests yet"
            description="Raise a service request for warranty support on a completed project."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>SR No</th>
                    <th>Project</th>
                    <th>Complaint</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($serviceRequests as $sr)
                    <tr wire:key="sr-{{ $sr->id }}">
                        <td class="font-mono">{{ $sr->sr_no }}</td>
                        <td>{{ $sr->warranty?->project?->name }}</td>
                        <td class="text-ink-900">{{ \Illuminate\Support\Str::limit($sr->complaint, 40) }}</td>
                        <td>{{ $sr->status->label() }}</td>
                        <td class="text-right">
                            <a href="{{ route('client.service-requests.show', $sr) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $serviceRequests->links() }}</div>
    @endif
</div>
