<div>
    <x-page-header title="Payroll" description="Generate and track monthly payroll runs per employee.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Payroll' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Payroll::class)
                <x-button href="{{ route('payroll.create') }}" wire:navigate>+ New Payroll</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Employee" name="employee" wire:model.live="employee">
                <option value="">Any employee</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->employee_id }} — {{ $emp->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-input label="Month" name="month" type="month" wire:model.live="month" />
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($payrolls->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No payroll records found"
                description="Generate a payroll run for an employee to see it listed here."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Month</th>
                    <th class="text-right">Net Salary</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payrolls as $payroll)
                    <tr wire:key="pay-{{ $payroll->id }}">
                        <td class="text-ink-900">{{ $payroll->employee?->name ?? '—' }}</td>
                        <td>{{ $payroll->month }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($payroll->net_salary, 2) }}</td>
                        <td>
                            <x-status-badge :label="$payroll->status->label()" :color="$payroll->status->color()" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            <a href="{{ route('payroll.show', $payroll) }}" wire:navigate class="dropdown-item">View</a>
                            @can('update', $payroll)
                                <a href="{{ route('payroll.edit', $payroll) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $payrolls->links() }}
        </div>
    @endif
</div>
