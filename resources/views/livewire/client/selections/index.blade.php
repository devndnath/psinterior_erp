<div>
    @if ($boards->isEmpty())
        <x-empty-state
            title="No selections yet"
            description="Material and finish choices shared with you for a decision will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Area</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boards as $board)
                    <tr wire:key="board-{{ $board->id }}">
                        <td class="font-medium text-ink-900">{{ $board->title }}</td>
                        <td>{{ $board->area ?: '—' }}</td>
                        <td>{{ $board->project?->name }}</td>
                        <td><x-status-badge :label="$board->status->label()" :color="$board->status->color()" /></td>
                        <td class="text-right">
                            <a href="{{ route('client.selections.show', $board) }}" wire:navigate class="text-accent-600 hover:underline">
                                {{ $board->status->value === 'decided' ? 'View' : 'Decide' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $boards->links() }}</div>
    @endif
</div>
