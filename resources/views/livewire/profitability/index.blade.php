<div>
    <x-breadcrumbs :items="['Profitability' => null]" class="mb-4" />

    <h1 class="mb-6 text-2xl font-semibold text-gray-900">Profitability</h1>

    <div class="card overflow-hidden">
        @if ($rows->isEmpty())
            <x-empty-state
                title="No projects yet"
                description="Profitability is calculated per project once projects exist."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Project</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Contract Value</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Invoiced</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Received</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Expenses</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Profit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($rows as $row)
                        <tr wire:key="profit-{{ $row['project']->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <a href="{{ route('projects.show', $row['project']) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $row['project']->project_code }}</a>
                                <span class="text-gray-500">— {{ $row['project']->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($row['contract_value'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($row['total_invoiced'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($row['total_received'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($row['total_expenses'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ $row['profit'] >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($row['profit'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $row['margin_percent'] === null ? '—' : number_format($row['margin_percent'], 2).'%' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
