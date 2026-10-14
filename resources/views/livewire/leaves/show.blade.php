@php($leaveTitle = ($leave->employee?->name ?? 'Employee')."'s {$leave->type->label()} Leave")
<div>
    <x-page-header :title="$leaveTitle">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Leaves' => route('leaves.index'), 'Request #'.$leave->id => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">{{ $leave->from_date->format('d M, Y') }} – {{ $leave->to_date->format('d M, Y') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Reason</h2>
                <p class="whitespace-pre-line text-sm text-ink-900">{{ $leave->reason ?: 'No reason provided.' }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Approval Status</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500">Manager</dt>
                        <dd><x-status-badge :label="$leave->manager_status->label()" :color="$leave->manager_status->color()" /></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500">HR</dt>
                        <dd><x-status-badge :label="$leave->hr_status->label()" :color="$leave->hr_status->color()" /></dd>
                    </div>
                </dl>

                <div class="mt-6 space-y-3 border-t border-ink-100 pt-4">
                    @can('approveByManager', $leave)
                        @if ($leave->manager_status->value === 'pending')
                            <div class="flex gap-2">
                                <button type="button" wire:click="approveByManager" class="btn-primary flex-1">Approve (Manager)</button>
                                <x-confirm-dialog
                                    title="Reject at manager stage?"
                                    message="This marks the manager-stage approval as rejected."
                                    action="rejectByManager"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="btn-secondary text-danger-600">Reject</button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                            </div>
                        @endif
                    @endcan

                    @can('approveByHr', $leave)
                        @if ($leave->hr_status->value === 'pending')
                            <div class="flex gap-2">
                                <button type="button" wire:click="approveByHr" class="btn-primary flex-1">Approve (HR)</button>
                                <x-confirm-dialog
                                    title="Reject at HR stage?"
                                    message="This marks the HR-stage approval as rejected."
                                    action="rejectByHr"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="btn-secondary text-danger-600">Reject</button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                            </div>
                        @endif
                    @endcan

                    @if ($leave->isFullyApproved())
                        <p class="text-sm font-medium text-success-600">Fully approved.</p>
                    @elseif ($leave->isRejected())
                        <p class="text-sm font-medium text-danger-600">Rejected.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
