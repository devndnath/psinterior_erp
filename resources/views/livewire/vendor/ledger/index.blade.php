<div>
    <p class="mb-4 text-sm text-ink-500">
        A read-only record of expenses logged against your vendor account. This is not a full
        invoicing or payment-status system — it reflects what has been recorded on the staff side.
    </p>

    @if ($expenses->isEmpty())
        <x-empty-state
            title="No expenses recorded yet"
            description="Expenses logged against your vendor account will appear here."
        />
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                    <tr wire:key="expense-{{ $expense->id }}">
                        <td>{{ $expense->date?->format('d M, Y') }}</td>
                        <td>{{ $expense->project?->name ?? '—' }}</td>
                        <td class="text-ink-900">{{ $expense->description }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $expenses->links() }}</div>
    @endif
</div>
