<div>
    <x-breadcrumbs :items="['Payroll' => null]" class="mb-4" />

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Payroll</h1>
        @can('create', \App\Models\Payroll::class)
            <a href="{{ route('payroll.create') }}" wire:navigate class="btn-primary">+ New Payroll</a>
        @endcan
    </div>

    <div class="card mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <label class="form-label">Employee</label>
            <select wire:model.live="employee" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any employee</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Month</label>
            <input wire:model.live="month" type="month" class="block rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>
        <div>
            <label class="form-label">Status</label>
            <select wire:model.live="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" wire:click="resetFilters" class="btn-secondary">Reset</button>
    </div>

    <div class="card overflow-hidden">
        @if ($payrolls->isEmpty())
            <x-empty-state
                title="No payroll records found"
                description="Generate a payroll run for an employee to see it listed here."
            />
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Month</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Net Salary</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($payrolls as $payroll)
                        <tr wire:key="pay-{{ $payroll->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payroll->employee?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $payroll->month }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($payroll->net_salary, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-status-badge :label="$payroll->status->label()" :color="$payroll->status->color()" />
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('payroll.show', $payroll) }}" wire:navigate class="text-gray-500 hover:text-gray-700">View</a>
                                @can('update', $payroll)
                                    <a href="{{ route('payroll.edit', $payroll) }}" wire:navigate class="ml-3 text-gray-500 hover:text-gray-700">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>
</div>
