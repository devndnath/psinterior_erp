<div>
    <x-page-header title="Profitability" description="Contract value, invoicing, expenses and margin per project.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Profitability' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    @if ($rows->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No projects yet"
                description="Profitability is calculated per project once projects exist."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th class="text-right">Contract Value</th>
                    <th class="text-right">Invoiced</th>
                    <th class="text-right">Received</th>
                    <th class="text-right">Expenses</th>
                    <th class="text-right">Profit</th>
                    <th class="text-right">Margin %</th>
                    <th class="text-right">Budget</th>
                    <th class="text-right">Budget Variance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr wire:key="profit-{{ $row['project']->id }}">
                        <td class="text-ink-900">
                            <a href="{{ route('projects.show', $row['project']) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $row['project']->project_code }}</a>
                            <span class="text-ink-500">— {{ $row['project']->name }}</span>
                        </td>
                        <td class="text-right">{{ number_format($row['contract_value'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['total_invoiced'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['total_received'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['total_expenses'], 2) }}</td>
                        <td class="text-right font-medium {{ $row['profit'] >= 0 ? 'text-success-700' : 'text-danger-600' }}">{{ number_format($row['profit'], 2) }}</td>
                        <td class="text-right">{{ $row['margin_percent'] === null ? '—' : number_format($row['margin_percent'], 2).'%' }}</td>
                        <td class="text-right">{{ $row['estimated_budget'] === null ? '—' : number_format($row['estimated_budget'], 2) }}</td>
                        <td class="text-right font-medium {{ $row['is_over_budget'] === true ? 'text-danger-600' : ($row['is_over_budget'] === false ? 'text-success-700' : '') }}">
                            @if ($row['budget_variance'] === null)
                                —
                            @else
                                {{ number_format($row['budget_variance'], 2) }}
                                @if ($row['budget_variance_percent'] !== null)
                                    <span class="text-ink-400">({{ number_format($row['budget_variance_percent'], 1) }}%)</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</div>
