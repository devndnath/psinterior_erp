<div>
    <x-page-header title="My Expense Claims" description="Submit conveyance, meal or other reimbursement claims from site visits and client meetings.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['My Expense Claims' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-button href="{{ route('my-expense-claims.create') }}" wire:navigate>+ New Claim</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    @if ($claims->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No expense claims yet"
                description="Submit a claim for conveyance or other out-of-pocket costs and it will show up here with its approval status."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Project / Site Visit</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($claims as $claim)
                    <tr wire:key="my-claim-{{ $claim->id }}">
                        <td>{{ $claim->date->format('d M, Y') }}</td>
                        <td class="text-ink-900">{{ $claim->type->label() }}</td>
                        <td class="text-ink-500">
                            {{ $claim->project?->project_code ?? ($claim->siteVisit?->sv_no ?? '—') }}
                        </td>
                        <td class="text-right font-medium text-ink-900">{{ number_format($claim->amount, 2) }}</td>
                        <td>
                            <x-status-badge :label="$claim->status->label()" :color="$claim->status->color()" />
                        </td>
                        <td class="text-right">
                            @if ($claim->isPending())
                                <x-confirm-dialog
                                    title="Cancel this claim?"
                                    message="This withdraws your claim before it has been reviewed."
                                    action="cancel"
                                    :params="[$claim->id]"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="text-sm font-medium text-danger-600 hover:underline">Cancel</button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                            @endif
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
