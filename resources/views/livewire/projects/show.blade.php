<div>
    <x-breadcrumbs :items="['Projects' => route('projects.index'), $project->project_code => null]" class="mb-4" />

    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                <a href="{{ route('clients.show', $project->client) }}" wire:navigate class="font-mono text-brand-600 hover:underline">{{ $project->project_code }}</a>
                — {{ $project->client?->name }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" wire:navigate class="btn-secondary">Edit</a>
            @endcan
        </div>
    </div>

    <div class="mb-6 card p-4">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium text-gray-700">Overall progress</span>
            <span class="text-gray-500">{{ $project->progress }}%</span>
        </div>
        <div class="mt-2">
            <x-progress-bar :value="$project->progress" :show-label="false" />
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Project details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900">{{ $project->type?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Location</dt><dd class="font-medium text-gray-900">{{ $project->location ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Start date</dt><dd class="font-medium text-gray-900">{{ $project->start_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Expected end</dt><dd class="font-medium text-gray-900">{{ $project->expected_end_date?->format('d M, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Contract value</dt><dd class="font-medium text-gray-900">{{ $project->contract_value ? number_format($project->contract_value, 2) : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Estimated budget</dt><dd class="font-medium text-gray-900">{{ $project->estimated_budget ? number_format($project->estimated_budget, 2) : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Project manager</dt><dd class="font-medium text-gray-900">{{ $project->projectManager?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Designer</dt><dd class="font-medium text-gray-900">{{ $project->designer?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Site engineer</dt><dd class="font-medium text-gray-900">{{ $project->siteEngineer?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Supervisor</dt><dd class="font-medium text-gray-900">{{ $project->supervisor?->name ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Description</dt><dd class="whitespace-pre-line font-medium text-gray-900">{{ $project->description ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Milestones</h2>
                    @can('update', $project)
                        <button type="button" wire:click="$toggle('showMilestoneForm')" class="text-sm text-brand-600 hover:underline">
                            {{ $showMilestoneForm ? 'Cancel' : '+ Add milestone' }}
                        </button>
                    @endcan
                </div>

                @if ($showMilestoneForm)
                    <form wire:submit="addMilestone" class="mb-5 grid grid-cols-2 gap-3 border-b border-gray-100 pb-5">
                        <div class="col-span-2">
                            <input wire:model="milestone_name" type="text" placeholder="Milestone name *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('milestone_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <input wire:model="milestone_due_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <select wire:model="milestone_priority" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @foreach (\App\Enums\LeadPriority::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select wire:model="milestone_assigned_to" class="col-span-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="">Unassigned</option>
                            @foreach ($staff as $person)
                                <option value="{{ $person->id }}">{{ $person->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary col-span-2">Add milestone</button>
                    </form>
                @endif

                @if ($milestones->isEmpty())
                    <p class="text-sm text-gray-500">No milestones yet.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($milestones as $milestone)
                            <li wire:key="milestone-{{ $milestone->id }}" class="rounded-lg border border-gray-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $milestone->name }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            {{ $milestone->assignee?->name ?? 'Unassigned' }}
                                            @if ($milestone->due_date)
                                                — due {{ $milestone->due_date->format('d M, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :label="$milestone->status->label()" :color="$milestone->status->color()" />
                                        @can('update', $project)
                                            <select wire:change="updateMilestoneStatus({{ $milestone->id }}, $event.target.value)" class="rounded-lg border-gray-300 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                                @foreach ($milestoneStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($milestone->status->value === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" wire:click="removeMilestone({{ $milestone->id }})" wire:confirm="Remove this milestone?" class="text-xs text-red-500 hover:text-red-700">✕</button>
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Tasks</h2>
                    @can('update', $project)
                        <button type="button" wire:click="$toggle('showTaskForm')" class="text-sm text-brand-600 hover:underline">
                            {{ $showTaskForm ? 'Cancel' : '+ Add task' }}
                        </button>
                    @endcan
                </div>

                @if ($showTaskForm)
                    <form wire:submit="addTask" class="mb-5 grid grid-cols-2 gap-3 border-b border-gray-100 pb-5">
                        <div class="col-span-2">
                            <input wire:model="task_name" type="text" placeholder="Task name *" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @error('task_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <select wire:model="task_milestone_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="">No milestone</option>
                            @foreach ($milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->name }}</option>
                            @endforeach
                        </select>
                        <input wire:model="task_due_date" type="date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <select wire:model="task_priority" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            @foreach (\App\Enums\LeadPriority::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select wire:model="task_assigned_to" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                            <option value="">Unassigned</option>
                            @foreach ($staff as $person)
                                <option value="{{ $person->id }}">{{ $person->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary col-span-2">Add task</button>
                    </form>
                @endif

                @if ($tasks->isEmpty())
                    <p class="text-sm text-gray-500">No tasks yet.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($tasks as $task)
                            <li wire:key="task-{{ $task->id }}" class="rounded-lg border border-gray-100 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $task->name }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">
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
                                            <select wire:change="updateTaskStatus({{ $task->id }}, $event.target.value)" class="rounded-lg border-gray-300 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                                @foreach ($taskStatuses as $value => $label)
                                                    <option value="{{ $value }}" @selected($task->status->value === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" wire:click="removeTask({{ $task->id }})" wire:confirm="Remove this task?" class="text-xs text-red-500 hover:text-red-700">✕</button>
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Site visits</h2>
                    @can('create', \App\Models\SiteVisit::class)
                        <a href="{{ route('site-visits.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-brand-600 hover:underline">+ Log visit</a>
                    @endcan
                </div>
                @if ($siteVisits->isEmpty())
                    <p class="text-sm text-gray-500">No site visits logged for this project yet.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($siteVisits as $visit)
                            <li class="flex items-center justify-between py-2 text-sm">
                                <a href="{{ route('site-visits.show', $visit) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $visit->sv_no }}</a>
                                <span class="text-gray-500">{{ $visit->visit_date?->format('d M, Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @can('view_designs')
                <div class="card p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Designs</h2>
                        @can('create', \App\Models\Design::class)
                            <a href="{{ route('designs.create', ['project' => $project->id]) }}" wire:navigate class="text-sm text-brand-600 hover:underline">+ Upload design</a>
                        @endcan
                    </div>
                    @if ($designs->isEmpty())
                        <p class="text-sm text-gray-500">No designs uploaded for this project yet.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($designs as $design)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <div>
                                        <a href="{{ route('designs.show', $design) }}" wire:navigate class="font-medium text-brand-600 hover:underline">{{ $design->design_no }}</a>
                                        <span class="text-gray-500">— {{ $design->title }}</span>
                                    </div>
                                    <x-status-badge :label="$design->status->label()" :color="$design->status->color()" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endcan

            <div class="card p-6 text-sm text-gray-400">
                BOQ, Quotations, Contracts, Change Orders, Invoices, Payments, Expenses, Meetings, Inspections, Snags and Handover will appear here as those modules ship (Phases 5+).
            </div>
        </div>

        <div>
            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Team</h2>
                    @can('update', $project)
                        <button type="button" wire:click="$toggle('showMemberForm')" class="text-sm text-brand-600 hover:underline">
                            {{ $showMemberForm ? 'Cancel' : '+ Add' }}
                        </button>
                    @endcan
                </div>

                @if ($showMemberForm)
                    <form wire:submit="addMember" class="mb-5 space-y-3 border-b border-gray-100 pb-5">
                        <div>
                            <select wire:model="member_user_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                                <option value="">Select a person…</option>
                                @foreach ($staff as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                            @error('member_user_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <input wire:model="member_role" type="text" placeholder="Role on project (e.g. Site carpenter lead)" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
                        <button type="submit" class="btn-primary w-full">Add to team</button>
                    </form>
                @endif

                @if ($members->isEmpty())
                    <p class="text-sm text-gray-500">No additional team members added.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($members as $member)
                            <li wire:key="member-{{ $member->id }}" class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    @if ($member->pivot->role_on_project)
                                        <p class="text-xs text-gray-500">{{ $member->pivot->role_on_project }}</p>
                                    @endif
                                </div>
                                @can('update', $project)
                                    <button type="button" wire:click="removeMember({{ $member->id }})" wire:confirm="Remove from the team?" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
