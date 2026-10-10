<div>
    <p class="mb-4 text-sm text-gray-500">
        A read-only record of expenses logged against your vendor account. This is not a full
        invoicing or payment-status system — it reflects what has been recorded on the staff side.
    </p>

    <div class="card overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($expenses as $expense)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $expense->date?->format('d M, Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $expense->project?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $expense->description }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No expenses recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>
</div>
