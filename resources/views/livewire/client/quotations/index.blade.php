<div>
    @if ($quotations->isEmpty())
        <x-empty-state
            title="No quotations yet"
            description="Quotations sent to you for review will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Quotation No</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th class="text-right">Grand Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotations as $quotation)
                    <tr wire:key="quotation-{{ $quotation->id }}">
                        <td class="font-mono">{{ $quotation->quotation_no }}</td>
                        <td>{{ $quotation->project?->name }}</td>
                        <td><x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" /></td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($quotation->grand_total, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('client.quotations.show', $quotation) }}" wire:navigate class="text-accent-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $quotations->links() }}</div>
    @endif
</div>
