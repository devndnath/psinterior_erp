<div>
    <x-page-header title="Expense Claims" description="Review and approve employee conveyance, meal and other reimbursement claims.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Expense Claims' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\ExpenseClaim::class)
                <x-button href="{{ route('expense-claims.create') }}" wire:navigate>+ New Claim</x-button>
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
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </div>

    @if ($claims->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No expense claims found"
                description="Employees submit these from My Expense Claims — they'll show up here for review."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($claims as $claim)
                    <tr wire:key="claim-{{ $claim->id }}">
                        <td class="text-ink-900">{{ $claim->employee?->name ?? '—' }}</td>
                        <td>{{ $claim->date->format('d M, Y') }}</td>
                        <td>{{ $claim->type->label() }}</td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($claim->amount, 2) }}</td>
                        <td>
                            <x-status-badge :label="$claim->status->label()" :color="$claim->status->color()" />
                        </td>
                        <td class="text-right">
                            <x-dropdown>
                                <a href="{{ route('expense-claims.show', $claim) }}" wire:navigate class="dropdown-item">View</a>
                                @can('update', $claim)
                                    <a href="{{ route('expense-claims.edit', $claim) }}" wire:navigate class="dropdown-item">Edit</a>
                                @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $claims->links() }}
        </div>
    @endif
</div>
