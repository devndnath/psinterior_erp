<div>
    <x-page-header :title="$project->name">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Projects' => route('projects.index'), $project->project_code => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
            @can('update', $project)
                <x-button variant="secondary" href="{{ route('projects.edit', $project) }}" wire:navigate>Edit</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <p class="-mt-4 mb-6 text-sm text-ink-500">
        <a href="{{ route('clients.show', $project->client) }}" wire:navigate class="font-mono text-accent-600 hover:underline">{{ $project->project_code }}</a>
        — {{ $project->client?->name }}
    </p>

    <div class="mb-6 panel p-4">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium text-ink-700">Overall progress</span>
            <span class="text-ink-500">{{ $project->progress }}%</span>
        </div>
        <div class="mt-2">
            <x-progress-bar :value="$project->progress" :show-label="false" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="panel p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Project details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-500">Type</dt><dd class="font-medium text-ink-900">{{ $project->type?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Location</dt><dd class="font-medium text-ink-900">{{ $project->location ?: '—' }}</dd></div>
                    <div><dt class="text-ink-500">Start date</dt><dd class="font-medium text-ink-900">{{ $project->start_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Expected end</dt><dd class="font-medium text-ink-900">{{ $project->expected_end_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Contract value</dt><dd class="font-medium text-ink-900">{{ $project->contract_value ? number_format($project->contract_value, 2) : '—' }}</dd></div>
                    <div><dt class="text-ink-500">Estimated budget</dt><dd class="font-medium text-ink-900">{{ $project->estimated_budget ? number_format($project->estimated_budget, 2) : '—' }}</dd></div>
                    <div><dt class="text-ink-500">Project manager</dt><dd class="font-medium text-ink-900">{{ $project->projectManager?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Designer</dt><dd class="font-medium text-ink-900">{{ $project->designer?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Site engineer</dt><dd class="font-medium text-ink-900">{{ $project->siteEngineer?->name ?? '—' }}</dd></div>
                    <div><dt class="text-ink-500">Supervisor</dt><dd class="font-medium text-ink-900">{{ $project->supervisor?->name ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-500">Description</dt><dd class="whitespace-pre-line font-medium text-ink-900">{{ $project->description ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Milestones</h2>
                    @can('update', $project)
                        <button type="button" wire:click="$toggle('showMilestoneForm')" class="text-sm text-accent-600 hover:underline">
                            {{ $showMilestoneForm ? 'Cancel' : '+ Add milestone' }}
                        </button>
                    @endcan
                </div>

                @if ($showMilestoneForm)
                    <form wire:submit="addMilestone" class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-3 border-b border-ink-100 pb-5">
                        <div class="col-span-2">
                            <x-input name="milestone_name" placeholder="Milestone name *" wire:model="milestone_name" />
                        </div>
                        <x-input name="milestone_due_date" type="date" wire:model="milestone_due_date" />
                        <x-select name="milestone_priority" wire:model="milestone_priority">
                            @foreach (\App\Enums\LeadPriority::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <div class="col-span-2">
                            <x-select name="milestone_assigned_to" wire:model="milestone_assigned_to">
                                <option value="">Unassigned</option>
                                @foreach ($staff as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-button type="submit" class="col-span-2">Add milestone</x-button>
                    </form>
                @endif

                @if ($milestones->isEmpty())
                    <p class="text-sm text-ink-500">No milestones yet.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($milestones as $milestone)
                            <li wire:key="milestone-{{ $milestone->id }}" class="rounded-lg border border-ink-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-ink-900">{{ $milestone->name }}</p>
                                        <p class="mt-0.5 text-xs text-ink-500">
                                            {{ $milestone->assignee?->name ?? 'Unassigned' }}
                                            @if ($milestone->due_date)
                                                — due {{ $milestone->due_date->format('d M, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :label="$milestone->status->label()" :color="$milestone->status->color()" />
                                        @can('update', $project)
                                            <select wire:change="updateMilestoneStatus({{ $milestone->id }}, $event.target.value)" class="rounded-md border-ink-200 text-xs shadow-sm focus:border-accent-500 focus:ring-accent-500">
                                                @foreach ($milestoneStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($milestone->status->value === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" wire:click="removeMilestone({{ $milestone->id }})" wire:confirm="Remove this milestone?" class="text-xs text-danger-500 hover:text-danger-700">✕</button>
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Tasks</h2>
                    @can('update', $project)
                        <button type="button" wire:click="$toggle('showTaskForm')" class="text-sm text-accent-600 hover:underline">
                            {{ $showTaskForm ? 'Cancel' : '+ Add task' }}
                        </button>
                    @endcan
                </div>

                @if ($showTaskForm)
                    <form wire:submit="addTask" class="mb-5 grid grid-cols-1 sm:grid-cols-2 gap-3 border-b border-ink-100 pb-5">
                        <div class="col-span-2">
                            <x-input name="task_name" placeholder="Task name *" wire:model="task_name" />
                        </div>
                        <x-select name="task_milestone_id" wire:model="task_milestone_id">
                            <option value="">No milestone</option>
                            @foreach ($milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input name="task_due_date" type="date" wire:model="task_due_date" />
                        <x-select name="task_priority" wire:model="task_priority">
                            @foreach (\App\Enums\LeadPriority::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-select name="task_assigned_to" wire:model="task_assigned_to">
                            <option value="">Unassigned</option>
                            @foreach ($staff as $person)
                                <option value="{{ $person->id }}">{{ $person->name }}</option>
                            @endforeach
                        </x-select>
                        <x-button type="submit" class="col-span-2">Add task</x-button>
                    </form>
                @endif

                @if ($tasks->isEmpty())
                    <p class="text-sm text-ink-500">No tasks yet.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($tasks as $task)
                            <li wire:key="task-{{ $task->id }}" class="rounded-lg border border-ink-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-ink-900">{{ $task->name }}</p>
                                        <p class="mt-0.5 text-xs text-ink-500">
                                            {{ $task->assignee?->name ?? 'Unassigned' }}
                                            @if ($task->milestone)
                                                — {{ $task->milestone->name }}
                                            @endif
                                            @if ($task->due_date)
                                                — due {{ $task->due_date->format('d M, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :label="$task->status->label()" :color="$task->status->color()" />
                                        @can('update', $project)
                                            <select wire:change="updateTaskStatus({{ $task->id }}, $event.target.value)" class="rounded-md border-ink-200 text-xs shadow-sm focus:border-accent-500 focus:ring-accent-500">
                                                @foreach ($taskStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($task->status->value === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" wire:click="removeTask({{ $task->id }})" wire:confirm="Remove this task?" class="text-xs text-danger-500 hover:text-danger-700">✕</button>
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Site visits</h2>
                    @can('create', \App\Models\SiteVisit::class)
                        <a href="{{ route('site-visits.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ Log visit</a>
                    @endcan
                </div>
                @if ($siteVisits->isEmpty())
                    <p class="text-sm text-ink-500">No site visits logged for this project yet.</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($siteVisits as $visit)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $visit->sv_no }}</a>
                                <span class="text-ink-500">{{ $visit->visit_date?->format('d M, Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @can('view_designs')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Designs</h2>
                        @can('create', \App\Models\Design::class)
                            <a href="{{ route('designs.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ Upload design</a>
                        @endcan
                    </div>
                    @if ($designs->isEmpty())
                        <p class="text-sm text-ink-500">No designs uploaded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($designs as $design)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('designs.show', $design) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $design->design_no }}</a>
                                        <span class="text-ink-500">— {{ $design->title }}</span>
                                    </div>
                                    <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_boqs')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">BOQs</h2>
                        @can('create', \App\Models\Boq::class)
                            <a href="{{ route('boqs.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New BOQ</a>
                        @endcan
                    </div>
                    @if ($boqs->isEmpty())
                        <p class="text-sm text-ink-500">No BOQs created for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($boqs as $boq)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('boqs.show', $boq) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $boq->boq_no }}</a>
                                        <span class="text-ink-500">v{{ $boq->version }} — {{ number_format($boq->grand_total, 2) }}</span>
                                    </div>
                                    <x-status-badge :label="$boq->status->label()" :color="$boq->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_quotations')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Quotations</h2>
                        @can('create', \App\Models\Quotation::class)
                            <a href="{{ route('quotations.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Quotation</a>
                        @endcan
                    </div>
                    @if ($quotations->isEmpty())
                        <p class="text-sm text-ink-500">No quotations created for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($quotations as $quotation)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('quotations.show', $quotation) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $quotation->quotation_no }}</a>
                                        <span class="text-ink-500">v{{ $quotation->version }} — {{ number_format($quotation->grand_total, 2) }}</span>
                                    </div>
                                    <x-status-badge :label="$quotation->status->label()" :color="$quotation->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_contracts')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Contracts</h2>
                        @can('create', \App\Models\Contract::class)
                            <a href="{{ route('contracts.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Contract</a>
                        @endcan
                    </div>
                    @if ($contracts->isEmpty())
                        <p class="text-sm text-ink-500">No contracts created for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($contracts as $contract)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('contracts.show', $contract) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $contract->contract_no }}</a>
                                        <span class="text-ink-500">{{ number_format($contract->amount, 2) }}</span>
                                    </div>
                                    <x-status-badge :label="$contract->status->label()" :color="$contract->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_change_orders')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Change Orders</h2>
                        @can('create', \App\Models\ChangeOrder::class)
                            <a href="{{ route('change-orders.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Change Order</a>
                        @endcan
                    </div>
                    @if ($changeOrders->isEmpty())
                        <p class="text-sm text-ink-500">No change orders recorded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($changeOrders as $changeOrder)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('change-orders.show', $changeOrder) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $changeOrder->co_no }}</a>
                                        <span class="text-ink-500">{{ number_format($changeOrder->cost, 2) }}</span>
                                    </div>
                                    <x-status-badge :label="$changeOrder->status->label()" :color="$changeOrder->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_invoices')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Invoices</h2>
                        @can('create', \App\Models\Invoice::class)
                            <a href="{{ route('invoices.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Invoice</a>
                        @endcan
                    </div>
                    @if ($invoices->isEmpty())
                        <p class="text-sm text-ink-500">No invoices raised for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($invoices as $invoice)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('invoices.show', $invoice) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $invoice->invoice_no }}</a>
                                        <span class="text-ink-500">{{ number_format($invoice->grand_total, 2) }}</span>
                                    </div>
                                    <x-status-badge :label="$invoice->status->label()" :color="$invoice->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_payments')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Payments</h2>
                        @can('create', \App\Models\Payment::class)
                            <a href="{{ route('payments.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ Record Payment</a>
                        @endcan
                    </div>
                    @if ($payments->isEmpty())
                        <p class="text-sm text-ink-500">No payments recorded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($payments as $payment)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('payments.show', $payment) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $payment->payment_no }}</a>
                                    <span class="text-ink-500">{{ number_format($payment->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_expenses')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Expenses</h2>
                        @can('create', \App\Models\Expense::class)
                            <a href="{{ route('expenses.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Expense</a>
                        @endcan
                    </div>
                    @if ($expenses->isEmpty())
                        <p class="text-sm text-ink-500">No expenses recorded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($expenses as $expense)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('expenses.show', $expense) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $expense->expense_no }}</a>
                                    <span class="text-ink-500">{{ number_format($expense->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_material_requisitions')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Material Requisitions</h2>
                        @can('create', \App\Models\MaterialRequisition::class)
                            <a href="{{ route('material-requisitions.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Requisition</a>
                        @endcan
                    </div>
                    @if ($materialRequisitions->isEmpty())
                        <p class="text-sm text-ink-500">No material requisitions raised for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($materialRequisitions as $mr)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('material-requisitions.show', $mr) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $mr->mr_no }}</a>
                                    <x-status-badge :label="$mr->status->label()" :color="$mr->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_purchase_orders')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Purchase Orders</h2>
                        @can('create', \App\Models\PurchaseOrder::class)
                            <a href="{{ route('purchase-orders.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Purchase Order</a>
                        @endcan
                    </div>
                    @if ($purchaseOrders->isEmpty())
                        <p class="text-sm text-ink-500">No purchase orders raised for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($purchaseOrders as $po)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('purchase-orders.show', $po) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $po->po_no }}</a>
                                    <div class="flex items-center gap-3">
                                        <span class="text-ink-500">{{ number_format($po->total, 2) }}</span>
                                        <x-status-badge :label="$po->status->label()" :color="$po->status->color()" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_subcontractors')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Subcontractor Assignments</h2>
                        @can('create', \App\Models\SubcontractorAssignment::class)
                            <a href="{{ route('subcontractor-assignments.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Assignment</a>
                        @endcan
                    </div>
                    @if ($subcontractorAssignments->isEmpty())
                        <p class="text-sm text-ink-500">No subcontractors assigned to this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($subcontractorAssignments as $assignment)
                                <li class="py-2 text-sm">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('subcontractor-assignments.show', $assignment) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $assignment->subcontractor?->name }}</a>
                                        <span class="text-ink-500">{{ number_format($assignment->rate, 2) }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <x-progress-bar :value="$assignment->progress" :show-label="false" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_meetings')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Meetings</h2>
                        @can('create', \App\Models\Meeting::class)
                            <a href="{{ route('meetings.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Meeting</a>
                        @endcan
                    </div>
                    @if ($meetings->isEmpty())
                        <p class="text-sm text-ink-500">No meetings recorded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($meetings as $meeting)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('meetings.show', $meeting) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $meeting->date->format('d M, Y') }} — {{ $meeting->type->label() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_inspections')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Inspections</h2>
                        @can('create', \App\Models\Inspection::class)
                            <a href="{{ route('inspections.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Inspection</a>
                        @endcan
                    </div>
                    @if ($inspections->isEmpty())
                        <p class="text-sm text-ink-500">No inspections recorded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($inspections as $inspection)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('inspections.show', $inspection) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $inspection->inspection_date->format('d M, Y') }} — {{ $inspection->area ?: 'General' }}</a>
                                    <x-status-badge :label="$inspection->result->label()" :color="$inspection->result->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_snags')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Snags</h2>
                        @can('create', \App\Models\Snag::class)
                            <a href="{{ route('snags.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Snag</a>
                        @endcan
                    </div>
                    @if ($snags->isEmpty())
                        <p class="text-sm text-ink-500">No snags raised for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($snags as $snag)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('snags.show', $snag) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $snag->snag_no }}</a>
                                    <x-status-badge :label="$snag->status->label()" :color="$snag->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            @can('view_handovers')
                <div class="panel p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Handover</h2>
                    @if ($handover)
                        <div class="flex items-center justify-between text-sm">
                            <a href="{{ route('handovers.show', $handover) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $handover->handover_date->format('d M, Y') }}</a>
                            @if ($handover->isConfirmed())
                                <x-status-badge label="Client Confirmed" color="green" />
                            @else
                                <x-status-badge label="Pending Confirmation" color="yellow" />
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-ink-500">
                            No handover yet.
                            @can('create', \App\Models\Handover::class)
                                <a href="{{ route('handovers.create', ['project' => $project->id]) }}" wire:navigate class="text-accent-600 hover:underline">Record Handover</a>
                            @endcan
                        </p>
                    @endif
                </div>
            @endcan

            @can('view_warranties')
                <div class="panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Warranties</h2>
                        @can('create', \App\Models\Warranty::class)
                            <a href="{{ route('warranties.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-accent-600 hover:underline">+ New Warranty</a>
                        @endcan
                    </div>
                    @if ($warranties->isEmpty())
                        <p class="text-sm text-ink-500">No warranties recorded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-ink-100">
                            @foreach ($warranties as $warranty)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <a href="{{ route('warranties.show', $warranty) }}" wire:navigate class="font-medium text-accent-600 hover:underline">{{ $warranty->start_date->format('d M, Y') }} – {{ $warranty->end_date->format('d M, Y') }}</a>
                                    @if ($warranty->isActive())
                                        <x-status-badge label="Active" color="green" />
                                    @else
                                        <x-status-badge label="Expired" color="gray" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan
        </div>

        <div>
            @can('view_finance')
                @if ($profitability)
                    <div class="panel mb-6 p-6">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-ink-500">Profitability</h2>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-ink-500">Contract value</dt><dd class="font-medium text-ink-900">{{ number_format($profitability['contract_value'], 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-ink-500">Expenses</dt><dd class="font-medium text-ink-900">{{ number_format($profitability['total_expenses'], 2) }}</dd></div>
                            <div class="flex justify-between border-t border-ink-100 pt-2">
                                <dt class="font-medium text-ink-700">Profit</dt>
                                <dd class="font-semibold {{ $profitability['profit'] >= 0 ? 'text-success-700' : 'text-danger-600' }}">{{ number_format($profitability['profit'], 2) }}</dd>
                            </div>
                            <div class="flex justify-between"><dt class="text-ink-500">Margin</dt><dd class="font-medium text-ink-900">{{ $profitability['margin_percent'] === null ? '—' : number_format($profitability['margin_percent'], 2).'%' }}</dd></div>
                        </dl>
                        <dl class="mt-4 space-y-2 border-t border-ink-100 pt-4 text-sm">
                            <div class="flex justify-between"><dt class="text-ink-500">Budget</dt><dd class="font-medium text-ink-900">{{ $profitability['estimated_budget'] === null ? 'Not set' : number_format($profitability['estimated_budget'], 2) }}</dd></div>
                            @if ($profitability['budget_variance'] !== null)
                                <div class="flex justify-between border-t border-ink-100 pt-2">
                                    <dt class="font-medium text-ink-700">Budget variance</dt>
                                    <dd class="font-semibold {{ $profitability['is_over_budget'] ? 'text-danger-600' : 'text-success-700' }}">
                                        {{ number_format($profitability['budget_variance'], 2) }}
                                        @if ($profitability['budget_variance_percent'] !== null)
                                            <span class="font-normal text-ink-400">({{ number_format($profitability['budget_variance_percent'], 1) }}%)</span>
                                        @endif
                                    </dd>
                                </div>
                                @if ($profitability['is_over_budget'])
                                    <p class="text-xs text-danger-600">Spending has exceeded the estimated budget.</p>
                                @endif
                            @endif
                        </dl>
                    </div>
                @endif
            @endcan

            <div class="panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-500">Team</h2>
                    @can('update', $project)
                        <button type="button" wire:click="$toggle('showMemberForm')" class="text-sm text-accent-600 hover:underline">
                            {{ $showMemberForm ? 'Cancel' : '+ Add' }}
                        </button>
                    @endcan
                </div>

                @if ($showMemberForm)
                    <form wire:submit="addMember" class="mb-5 space-y-3 border-b border-ink-100 pb-5">
                        <div>
                            <x-select name="member_user_id" wire:model="member_user_id">
                                <option value="">Select a person…</option>
                                @foreach ($staff as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-input name="member_role" placeholder="Role on project (e.g. Site carpenter lead)" wire:model="member_role" />
                        <x-button type="submit" class="w-full">Add to team</x-button>
                    </form>
                @endif

                @if ($members->isEmpty())
                    <p class="text-sm text-ink-500">No additional team members added.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($members as $member)
                            <li wire:key="member-{{ $member->id }}" class="flex items-center justify-between rounded-lg border border-ink-100 p-3">
                                <div>
                                    <p class="text-sm font-medium text-ink-900">{{ $member->name }}</p>
                                    @if ($member->pivot->role_on_project)
                                        <p class="text-xs text-ink-500">{{ $member->pivot->role_on_project }}</p>
                                    @endif
                                </div>
                                @can('update', $project)
                                    <button type="button" wire:click="removeMember({{ $member->id }})" wire:confirm="Remove from the team?" class="text-xs text-danger-500 hover:text-danger-700">Remove</button>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
