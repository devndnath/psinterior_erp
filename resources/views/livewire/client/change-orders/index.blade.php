<div>
    @if ($changeOrders->isEmpty())
        <x-empty-state
            title="No change orders yet"
            description="Change orders raised against your projects will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>CO No</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th class="text-right">Cost</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($changeOrders as $co)
                    <tr wire:key="co-{{ $co->id }}">
                        <td class="font-mono">{{ $co->co_no }}</td>
                        <td>{{ $co->project?->name }}</td>
                        <td><x-status-badge :label="$co->status->label()" :color="$co->status->color()" /></td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($co->cost, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('client.change-orders.show', $co) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $changeOrders->links() }}</div>
    @endif
</div>
