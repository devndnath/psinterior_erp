<div>
    <x-page-header title="Projects" description="Track every active and completed project from kickoff to handover.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Projects' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\Project::class)
                <x-button href="{{ route('projects.create') }}" wire:navigate>+ New Project</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Money at a glance for every project matching the filters below.
         Budget = contract value (or estimated budget before a contract);
         Balance = paid minus the projects' expenses. --}}
    @if ($summary)
        @php $currency = \App\Models\Setting::get('finance.currency', 'BDT'); @endphp
        <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Total Budget', 'value' => $summary['budget'], 'icon' => 'briefcase', 'note' => number_format($summary['count']).' '.\Illuminate\Support\Str::plural('project', $summary['count']), 'tone' => 'ink'],
                ['label' => 'Paid', 'value' => $summary['paid'], 'icon' => 'arrow-down-right', 'note' => $summary['budget'] > 0 ? round($summary['paid'] / $summary['budget'] * 100).'% of budget received' : 'received from clients', 'tone' => 'green'],
                ['label' => 'Expenses', 'value' => $summary['expenses'], 'icon' => 'wallet', 'note' => $summary['paid'] > 0 ? round($summary['expenses'] / $summary['paid'] * 100).'% of paid amount' : 'spent on these projects', 'tone' => 'gold'],
                ['label' => 'Balance', 'value' => $summary['balance'], 'icon' => 'landmark', 'note' => 'paid − expenses', 'tone' => $summary['balance'] < 0 ? 'red' : 'dark'],
            ] as $card)
                <div @class([
                    'rounded-3xl p-4 sm:p-5',
                    'bg-charcoal text-white dark:bg-white/[0.06]' => $card['tone'] === 'dark',
                    'bg-danger-600 text-white' => $card['tone'] === 'red',
                    'panel' => ! in_array($card['tone'], ['dark', 'red'], true),
                ])>
                    <div class="flex items-center justify-between gap-2">
                        <p @class(['text-xs font-medium', 'text-white/70' => in_array($card['tone'], ['dark', 'red'], true), 'text-ink-500' => ! in_array($card['tone'], ['dark', 'red'], true)])>{{ $card['label'] }}</p>
                        <span @class([
                            'flex h-8 w-8 items-center justify-center rounded-full',
                            'bg-white/10 text-accent-400' => in_array($card['tone'], ['dark', 'red'], true),
                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $card['tone'] === 'green',
                            'bg-accent-500/15 text-accent-700 dark:text-accent-400' => $card['tone'] === 'gold',
                            'bg-ink-100 text-ink-600' => $card['tone'] === 'ink',
                        ])>
                            <x-icon :name="$card['icon']" class="h-4 w-4" />
                        </span>
                    </div>
                    <p class="mt-2 flex items-baseline gap-1 truncate">
                        <span @class(['text-xs font-semibold', 'text-white/60' => in_array($card['tone'], ['dark', 'red'], true), 'text-ink-400' => ! in_array($card['tone'], ['dark', 'red'], true)])>{{ $currency }}</span>
                        <span @class(['text-lg font-bold tracking-tight tabular-nums sm:text-2xl', 'text-accent-400' => $card['tone'] === 'dark', 'text-ink-900' => ! in_array($card['tone'], ['dark', 'red'], true)])>{{ number_format((int) $card['value']) }}<span class="max-sm:hidden">.{{ str_pad((string) round(fmod(abs($card['value']), 1) * 100), 2, '0', STR_PAD_LEFT) }}</span></span>
                    </p>
                    <p @class(['mt-1 truncate text-[11px]', 'text-white/60' => in_array($card['tone'], ['dark', 'red'], true), 'text-ink-500' => ! in_array($card['tone'], ['dark', 'red'], true)])>{{ $card['note'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <x-filter-panel>
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name, project code, client…" />
        </div>
        <div>
            <x-select label="Status" name="status" wire:model.live="status">
                <option value="">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Project manager" name="projectManager" wire:model.live="projectManager">
                <option value="">Anyone</option>
                @foreach ($managers as $manager)
                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </x-filter-panel>

    @if ($projects->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No projects found"
                description="Open a project directly, or create one from a client's page once they're ready to proceed."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th class="th-sortable" wire:click="sortBy('project_code')">Project #</th>
                    <th class="th-sortable" wire:click="sortBy('name')">Name / Client</th>
                    <th class="th-sortable" wire:click="sortBy('status')">Status</th>
                    @if ($showMoney)
                        <th class="th-sortable !text-right" wire:click="sortBy('budget')">Budget</th>
                        <th class="th-sortable !text-right" wire:click="sortBy('paid')">Paid</th>
                        <th class="th-sortable !text-right" wire:click="sortBy('balance')" title="Paid minus the project's expenses">Balance</th>
                    @endif
                    <th>Progress</th>
                    <th>Manager</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr wire:key="project-{{ $project->id }}">
                        <td class="font-mono">
                            <a href="{{ route('projects.show', $project) }}" wire:navigate class="text-accent-600 hover:underline">{{ $project->project_code }}</a>
                        </td>
                        <td class="max-w-[260px]">
                            <span class="block truncate font-medium text-ink-900">{{ $project->name }}</span>
                            <span class="block truncate text-xs text-ink-500">{{ $project->client?->name ?? '—' }}</span>
                        </td>
                        <td>
                            <x-status-badge :label="$project->status->label()" :color="$project->status->color()" />
                        </td>
                        @if ($showMoney)
                            @php
                                $paid = (float) $project->paid_sum;
                                $balance = $paid - (float) $project->expense_sum;
                                $budget = (float) $project->budget_value;
                            @endphp
                            <td class="whitespace-nowrap text-right tabular-nums text-ink-900">
                                {{ $budget > 0 ? number_format($budget, 2) : '—' }}
                            </td>
                            <td class="whitespace-nowrap text-right tabular-nums">
                                <span class="text-ink-900">{{ number_format($paid, 2) }}</span>
                                @if ($budget > 0)
                                    <span class="block text-[11px] text-ink-500">{{ round($paid / $budget * 100) }}% of budget</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-right tabular-nums" title="Paid {{ number_format($paid, 2) }} − expenses {{ number_format((float) $project->expense_sum, 2) }}">
                                <span @class(['font-semibold', 'text-danger-600' => $balance < 0, 'text-success-700 dark:text-success-400' => $balance > 0, 'text-ink-500' => $balance == 0])>{{ number_format($balance, 2) }}</span>
                                <span class="block text-[11px] text-ink-500">exp. {{ number_format((float) $project->expense_sum, 2) }}</span>
                            </td>
                        @endif
                        <td>
                            <x-progress-bar :value="$project->progress" />
                        </td>
                        <td>{{ $project->projectManager?->name ?? '—' }}</td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single
                                 "..." action menu instead of several
                                 separate text links in every row. --}}
                            <x-dropdown>
                                <a href="{{ route('projects.show', $project) }}" wire:navigate class="dropdown-item">View</a>
                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}" wire:navigate class="dropdown-item">Edit</a>
                                @endcan
                                @can('delete', $project)
                                    <x-confirm-dialog
                                        title="Delete this project?"
                                        :message='"This will remove {$project->name} ({$project->project_code}) and its milestones/tasks. This cannot be undone."'
                                        action="deleteProject"
                                        :params="[$project->id]"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="dropdown-item-danger">Delete</button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                @endcan
                            </x-dropdown>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    @endif
</div>
