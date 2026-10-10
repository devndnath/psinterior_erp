<div>
    <x-breadcrumbs :items="['Payroll' => route('payroll.index'), $payroll->month => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $payroll->employee?->name }} — {{ $payroll->month }}</h1>
                <x-status-badge :label="$payroll->status->label()" :color="$payroll->status->color()" />
            </div>
        </div>
        <div class="flex items-center gap-3">
            @can('update', $payroll)
                <a href="{{ route('payroll.edit', $payroll) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
            @can('finalize', $payroll)
                @if ($payroll->status->value === 'draft')
                    <button type="button" wire:click="finalize" class="btn-secondary">Finalize</button>
                @endif
            @endcan
            @can('markPaid', $payroll)
                @if ($payroll->status->value === 'finalized')
                    <button type="button" wire:click="markPaid" class="btn-primary">Mark Paid</button>
                @endif
            @endcan
            @can('delete', $payroll)
                <x-confirm-dialog
                    title="Delete this payroll?"
                    message="This cannot be undone."
                    action="deletePayroll"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-red-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Breakdown</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="px-3 py-2 text-sm text-gray-500">Basic</td><td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payroll->basic, 2) }}</td></tr>
                        <tr><td class="px-3 py-2 text-sm text-gray-500">Allowance</td><td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payroll->allowance, 2) }}</td></tr>
                        <tr><td class="px-3 py-2 text-sm text-gray-500">Overtime</td><td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payroll->overtime, 2) }}</td></tr>
                        <tr><td class="px-3 py-2 text-sm text-gray-500">Bonus</td><td class="px-3 py-2 text-right text-sm font-medium text-gray-900">{{ number_format($payroll->bonus, 2) }}</td></tr>
                        <tr><td class="px-3 py-2 text-sm text-gray-500">Deduction</td><td class="px-3 py-2 text-right text-sm font-medium text-red-600">-{{ number_format($payroll->deduction, 2) }}</td></tr>
                        <tr><td class="px-3 py-2 text-sm text-gray-500">Advance</td><td class="px-3 py-2 text-right text-sm font-medium text-red-600">-{{ number_format($payroll->advance, 2) }}</td></tr>
                        <tr class="bg-gray-50"><td class="px-3 py-2 text-sm font-semibold text-gray-900">Net Salary</td><td class="px-3 py-2 text-right text-sm font-semibold text-gray-900">{{ number_format($payroll->net_salary, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Employee</h2>
                @if ($payroll->employee)
                    <a href="{{ route('employees.show', $payroll->employee) }}" wire:navigate class="text-sm font-medium text-brand-600 hover:underline">{{ $payroll->employee->name }}</a>
                    <p class="text-sm text-gray-500">{{ $payroll->employee->employee_id }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
