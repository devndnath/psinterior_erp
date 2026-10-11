<div>
    @if ($designs->isEmpty())
        <x-empty-state
            title="No designs yet"
            description="Designs shared with you for review will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Design No</th>
                    <th>Title</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($designs as $design)
                    <tr wire:key="design-{{ $design->id }}">
                        <td class="font-mono">{{ $design->design_no }}</td>
                        <td class="font-medium text-ink-900">{{ $design->title }}</td>
                        <td>{{ $design->project?->name }}</td>
                        <td><x-status-badge :label="$design->status->label()" :color="$design->status->color()" /></td>
                        <td class="text-right">
                            <a href="{{ route('client.designs.show', $design) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $designs->links() }}</div>
    @endif
</div>
