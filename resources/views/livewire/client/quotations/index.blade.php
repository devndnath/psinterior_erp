<div>
    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Quotation No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Grand Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($quotations as $quotation)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $quotation->quotation_no }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $quotation->project?->name }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" /></td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($quotation->grand_total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('client.quotations.show', $quotation) }}" wire:navigate class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No quotations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $quotations->links() }}</div>
</div>
