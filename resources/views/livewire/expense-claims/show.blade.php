@php($claimTitle = ($claim->employee?->name ?? 'Employee')."'s {$claim->type->label()} Claim")
<div>
    <x-page-header :title="$claimTitle">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Expense Claims' => route('expense-claims.index'), 'Claim #'.$claim->id => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$claim->status->label()" :color="$claim->status->color()" />
            @can('update', $claim)
                <x-button variant="secondary" href="{{ route('expense-claims.edit', $claim) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $claim)
                <x-confirm-dialog
                    title="Delete this claim?"
                    message="This cannot be undone."
                    action="deleteClaim"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $claim->date->format('d M, Y') }} · {{ number_format($claim->amount, 2) }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-ink-500">Project</dt>
                        <dd class="text-ink-900">
                            @if ($claim->project)
                                <a href="{{ route('projects.show', $claim->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $claim->project->project_code }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-ink-500">Site Visit</dt>
                        <dd class="text-ink-900">{{ $claim->siteVisit?->sv_no ?? '—' }}</dd>
                    </div>
                </dl>
                <h3 class="mb-2 mt-6 text-sm font-semibold uppercase tracking-wide text-ink-500">Description</h3>
                <p class="whitespace-pre-line text-sm text-ink-900">{{ $claim->description ?: 'No description provided.' }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Review</h2>
                @if ($claim->employee)
                    <a href="{{ route('employees.show', $claim->employee) }}" wire:navigate class="text-sm font-medium text-accent-600 hover:underline">{{ $claim->employee->name }}</a>
                    <p class="mb-4 text-sm text-ink-500">{{ $claim->employee->employee_id }}</p>
                @endif

                @if ($claim->reviewedBy)
                    <p class="text-sm text-ink-500">Reviewed by {{ $claim->reviewedBy->name }} on {{ $claim->reviewed_at?->format('d M, Y') }}</p>
                @endif

                @can('approve', $claim)
                    @if ($claim->isPending())
                        <div class="mt-4 flex gap-2">
                            <button type="button" wire:click="approve" class="btn-primary flex-1">Approve</button>
                            <x-confirm-dialog
                                title="Reject this claim?"
                                message="The employee will see this claim marked as rejected."
                                action="reject"
                            >
                                <x-slot:trigger>
                                    <button type="button" class="btn-secondary text-danger-600">Reject</button>
                                </x-slot:trigger>
                            </x-confirm-dialog>
                        </div>
                    @endif
                @endcan

                @if ($claim->payroll)
                    <p class="mt-4 text-sm text-success-600">
                        Included in <a href="{{ route('payroll.show', $claim->payroll) }}" wire:navigate class="hover:underline">{{ $claim->payroll->month }} payroll</a>.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
