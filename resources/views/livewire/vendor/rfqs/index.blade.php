<div>
    <p class="mb-4 text-sm text-ink-500">Purchase requests open for quotation, and the outcome of ones you've quoted on before.</p>

    @if ($requests->isEmpty())
        <x-empty-state
            title="No RFQs yet"
            description="Purchase requests sent for quotation will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Your quote</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $pr)
                    @php($mine = $pr->vendorQuotations->first())
                    <tr wire:key="pr-{{ $pr->id }}">
                        <td class="font-mono">{{ $pr->pr_no }}</td>
                        <td>{{ $pr->project?->name ?? '—' }}</td>
                        <td><x-status-badge :label="$pr->status->label()" :color="$pr->status->color()" /></td>
                        <td>
                            @if ($mine)
                                {{ number_format($mine->total_amount, 2) }}
                                <x-status-badge :label="$mine->status->label()" :color="$mine->status->color()" />
                            @else
                                <span class="text-ink-400">Not quoted</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('vendor.rfqs.show', $pr) }}" wire:navigate class="text-accent-600 hover:underline">
                                {{ $mine ? 'View' : 'Respond' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</div>
