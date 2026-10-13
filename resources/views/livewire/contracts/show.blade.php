<div>
    <x-page-header :title="$contract->contract_no">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Contracts' => route('contracts.index'), $contract->contract_no => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$contract->status->label()" :color="$contract->status->color()" />
            <x-button variant="secondary" wire:click="downloadPdf">Download PDF</x-button>
            @can('update', $contract)
                @if ($contract->status->value !== 'active')
                    <button type="button" wire:click="activate" wire:confirm="Activate this contract? This will update the project's contract value." class="btn-secondary">Activate</button>
                @endif
                <x-button variant="secondary" href="{{ route('contracts.edit', $contract) }}" wire:navigate>Edit</x-button>
            @endcan
            @can('delete', $contract)
                <x-confirm-dialog
                    title="Delete this contract?"
                    :message='"This will remove {$contract->contract_no} and its items. This cannot be undone."'
                    action="deleteContract"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-secondary text-danger-600">Delete</button>
                    </x-slot:trigger>
                </x-confirm-dialog>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        {{ $contract->client?->name }}
        @if ($contract->project)
            — <a href="{{ route('projects.show', $contract->project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $contract->project->project_code }}</a>
        @endif
        @if ($contract->quotation)
            — from <a href="{{ route('quotations.show', $contract->quotation) }}" wire:navigate class="hover:underline">{{ $contract->quotation->quotation_no }}</a>
        @endif
    </p>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Line items</h2>
                @if ($contract->items->isEmpty())
                    <p class="text-sm text-ink-500">No line items recorded.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($contract->items as $item)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <span class="text-ink-900">{{ $item->description }}</span>
                                <span class="font-medium text-ink-900">{{ number_format($item->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-4 flex justify-between border-t border-ink-100 pt-4 text-base">
                    <span class="font-semibold text-ink-700">Contract Amount</span>
                    <span class="font-semibold text-ink-900">{{ number_format($contract->amount, 2) }}</span>
                </div>
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Payment Schedule</h2>
                    @can('update', $contract)
                        <button type="button" wire:click="toggleScheduleForm" class="text-sm text-accent-600 hover:underline">
                            {{ $showScheduleForm ? 'Cancel' : '+ Add installment' }}
                        </button>
                    @endcan
                </div>

                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs text-ink-500">Scheduled</p>
                        <p class="text-sm font-semibold text-ink-900">{{ number_format($scheduleSummary['total_scheduled'], 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs text-ink-500">Due by Today</p>
                        <p class="text-sm font-semibold text-ink-900">{{ number_format($scheduleSummary['due_by_today'], 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs text-ink-500">Invoiced So Far</p>
                        <p class="text-sm font-semibold text-ink-900">{{ number_format($scheduleSummary['invoiced_so_far'], 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs text-ink-500">Collected So Far</p>
                        <p class="text-sm font-semibold text-ink-900">{{ number_format($scheduleSummary['collected_so_far'], 2) }}</p>
                    </div>
                </div>

                @if ($scheduleSummary['variance_due_vs_invoiced'] < 0)
                    <div class="mb-4 rounded-lg bg-warning-50 p-3 text-sm text-warning-800">
                        Behind schedule: {{ number_format(abs($scheduleSummary['variance_due_vs_invoiced']), 2) }} was due by today but hasn't been invoiced yet.
                    </div>
                @endif

                @if ($showScheduleForm)
                    <form wire:submit="saveScheduleLine" class="mb-6 space-y-3 rounded-lg border border-ink-100 p-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <x-input label="Label *" name="scheduleLabel" wire:model="scheduleLabel" placeholder="e.g. Advance on signing" />
                            </div>
                            <x-input label="Percentage of contract" name="schedulePercentage" type="number" step="0.01" min="0" max="100" wire:model="schedulePercentage" placeholder="e.g. 30" />
                            <x-input label="Fixed amount" name="scheduleAmount" type="number" step="0.01" min="0" wire:model="scheduleAmount" placeholder="Leave blank to compute from %" />
                            <x-input label="Due date" name="scheduleDueDate" type="date" wire:model="scheduleDueDate" />
                            <x-select label="Or due on milestone" name="scheduleMilestoneId" wire:model="scheduleMilestoneId">
                                <option value="">None</option>
                                @foreach ($milestones as $milestone)
                                    <option value="{{ $milestone->id }}">{{ $milestone->name }}</option>
                                @endforeach
                            </x-select>
                            <div class="col-span-2">
                                <x-textarea label="Notes" name="scheduleNotes" rows="2" wire:model="scheduleNotes" />
                            </div>
                        </div>
                        <x-button type="submit">{{ $editingLineId ? 'Save changes' : 'Add installment' }}</x-button>
                    </form>
                @endif

                @if ($contract->scheduleLines->isEmpty())
                    <p class="text-sm text-ink-500">No installments planned yet.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($contract->scheduleLines as $line)
                            <li wire:key="schedule-line-{{ $line->id }}" class="py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-ink-900">
                                            {{ $line->label }}
                                            @if ($line->percentage)
                                                <span class="text-ink-500">({{ rtrim(rtrim((string) $line->percentage, '0'), '.') }}%)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-ink-500">
                                            @if ($line->due_date)
                                                Due {{ $line->due_date->format('d M, Y') }}
                                            @endif
                                            @if ($line->milestone)
                                                · On milestone: {{ $line->milestone->name }}
                                            @endif
                                            @if ($line->isOverdue())
                                                · <span class="text-danger-600">Overdue, not yet invoiced</span>
                                            @endif
                                        </p>
                                        @if ($line->notes)
                                            <p class="mt-1 text-xs text-ink-500">{{ $line->notes }}</p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 text-sm font-semibold text-ink-900">{{ number_format($line->amount, 2) }}</p>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                                    @if ($line->invoice)
                                        <span class="inline-flex items-center gap-1">
                                            Invoiced as
                                            <a href="{{ route('invoices.show', $line->invoice) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $line->invoice->invoice_no }}</a>
                                            <x-status-badge :label="$line->invoice->status->label()" :color="$line->invoice->status->color()" />
                                        </span>
                                        @can('update', $contract)
                                            <button type="button" wire:click="unlinkInvoice({{ $line->id }})" class="text-ink-500 hover:underline">Unlink</button>
                                        @endcan
                                    @else
                                        <span class="text-ink-400">Not yet invoiced</span>
                                        @can('update', $contract)
                                            @if ($contract->invoices->isNotEmpty())
                                                @if ($linkingLineId === $line->id)
                                                    <form wire:submit="linkInvoice" class="flex items-start gap-2">
                                                        <div class="min-w-[14rem]">
                                                            <x-select name="linkInvoiceId" wire:model="linkInvoiceId" class="text-xs">
                                                                <option value="">Select invoice…</option>
                                                                @foreach ($contract->invoices as $invoice)
                                                                    <option value="{{ $invoice->id }}">{{ $invoice->invoice_no }} — {{ number_format($invoice->grand_total, 2) }}</option>
                                                                @endforeach
                                                            </x-select>
                                                        </div>
                                                        <button type="submit" class="pt-2 text-accent-600 hover:underline">Link</button>
                                                        <button type="button" wire:click="cancelLinkInvoiceForm" class="pt-2 text-ink-500 hover:underline">Cancel</button>
                                                    </form>
                                                @else
                                                    <button type="button" wire:click="openLinkInvoiceForm({{ $line->id }})" class="text-accent-600 hover:underline">Link an invoice</button>
                                                @endif
                                            @endif
                                        @endcan
                                    @endif

                                    @can('update', $contract)
                                        <button type="button" wire:click="editScheduleLine({{ $line->id }})" class="text-ink-500 hover:underline">Edit</button>
                                        @if (! $line->isInvoiced())
                                            <button type="button" wire:click="removeScheduleLine({{ $line->id }})" wire:confirm="Remove this installment?" class="text-danger-600 hover:underline">Remove</button>
                                        @endif
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Details</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-ink-500">Start date</dt><dd class="font-medium text-ink-900">{{ $contract->start_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">End date</dt><dd class="font-medium text-ink-900">{{ $contract->end_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Payment terms</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $contract->payment_terms ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Warranty terms</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $contract->warranty_terms ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Terms</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $contract->terms ?: '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
