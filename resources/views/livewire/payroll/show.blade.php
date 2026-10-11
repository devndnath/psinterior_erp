<div>
    <x-page-header :title="($payroll->employee?->name).' — '.$payroll->month">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payroll' => route('payroll.index'), $payroll->month => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$payroll->status->label()" :color="$payroll->status->color()" />
            @can('update', $payroll)
                <x-button variant="secondary" href="{{ route('payroll.edit', $payroll) }}" wire:navigate>Edit</x-button>
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
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Breakdown</h2>
                <table class="table-base">
                    <tbody>
                        <tr><td class="text-ink-500">Basic</td><td class="text-right font-medium text-ink-900">{{ number_format($payroll->basic, 2) }}</td></tr>
                        <tr><td class="text-ink-500">Allowance</td><td class="text-right font-medium text-ink-900">{{ number_format($payroll->allowance, 2) }}</td></tr>
                        <tr><td class="text-ink-500">Overtime</td><td class="text-right font-medium text-ink-900">{{ number_format($payroll->overtime, 2) }}</td></tr>
                        <tr><td class="text-ink-500">Bonus</td><td class="text-right font-medium text-ink-900">{{ number_format($payroll->bonus, 2) }}</td></tr>
                        <tr><td class="text-ink-500">Deduction</td><td class="text-right font-medium text-danger-600">-{{ number_format($payroll->deduction, 2) }}</td></tr>
                        <tr><td class="text-ink-500">Advance</td><td class="text-right font-medium text-danger-600">-{{ number_format($payroll->advance, 2) }}</td></tr>
                        <tr class="bg-ink-50"><td class="font-semibold text-ink-900">Net Salary</td><td class="text-right font-semibold text-ink-900">{{ number_format($payroll->net_salary, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Employee</h2>
                @if ($payroll->employee)
                    <a href="{{ route('employees.show', $payroll->employee) }}" wire:navigate class="text-sm font-medium text-accent-600 hover:underline">{{ $payroll->employee->name }}</a>
                    <p class="text-sm text-ink-500">{{ $payroll->employee->employee_id }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
