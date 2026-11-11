@php($callSvc = app(\App\Services\CallService::class))
<div>
    <x-page-header title="Calls" description="Every call and WhatsApp follow-up — leads and clients — in one list.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Meetings' => route('meetings.index'), 'Calls' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @if ($canLog)
                <x-button variant="secondary" wire:click="openForm('done')"><x-icon name="plus" class="h-4 w-4" /> Log call</x-button>
                <x-button wire:click="openForm('pending')"><x-icon name="phone" class="h-4 w-4" /> Schedule call</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-meetings-tabs active="calls" />

    {{-- At a glance — each tile filters the list ------------------------------ --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        @foreach ([
            'overdue' => ['Overdue', 'calls past their date', 'alert-triangle', 'bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-300'],
            'today' => ['Today', 'calls to make today', 'phone', 'bg-accent-500/15 text-accent-700 dark:text-accent-400'],
            'week' => ['Next 7 days', 'scheduled this week', 'calendar', 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300'],
            'history' => ['Logged', 'calls made this month', 'check', 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'],
        ] as $key => [$label, $sub, $icon, $chip])
            <button type="button" wire:click="$set('when', '{{ $key }}')" @class([
                'panel p-4 text-left transition hover:border-ink-300 sm:p-5',
                '!border-charcoal ring-1 ring-charcoal dark:!border-accent-500 dark:ring-accent-500' => $when === $key || ($when === 'todo' && in_array($key, ['overdue', 'today'])),
            ])>
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500">{{ $label }}</p>
                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $chip }}"><x-icon :name="$icon" class="h-4 w-4" /></span>
                </div>
                <p @class(['mt-1 text-2xl font-bold tracking-tight tabular-nums', 'text-danger-600' => $key === 'overdue' && $counts[$key] > 0, 'text-ink-900' => ! ($key === 'overdue' && $counts[$key] > 0)])>{{ number_format($counts[$key]) }}</p>
                <p class="mt-0.5 truncate text-[11px] text-ink-500">{{ $sub }}</p>
            </button>
        @endforeach
    </div>

    {{-- Log / schedule form ---------------------------------------------------- --}}
    @if ($showForm)
        <form wire:submit="save" class="panel mb-4 p-5 sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="section-title">{{ $mode === 'pending' ? 'Schedule a call' : 'Log a call' }}</p>
                <div class="inline-flex rounded-full border border-ink-200/70 bg-surface p-1 text-xs font-medium">
                    <button type="button" wire:click="$set('mode', 'done')" @class(['rounded-full px-3 py-1.5', 'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $mode === 'done', 'text-ink-500' => $mode !== 'done'])>Log a call I made</button>
                    <button type="button" wire:click="$set('mode', 'pending')" @class(['rounded-full px-3 py-1.5', 'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $mode === 'pending', 'text-ink-500' => $mode !== 'pending'])>Schedule a call</button>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <x-select label="Lead or client *" name="contact" wire:model="contact">
                        <option value="">Choose…</option>
                        @if ($pickClients->isNotEmpty())
                            <optgroup label="Clients">
                                @foreach ($pickClients as $c)
                                    <option value="client:{{ $c->id }}">{{ $c->name }} · {{ $c->client_no }}{{ $c->phone ? ' · '.$c->phone : '' }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if ($pickLeads->isNotEmpty())
                            <optgroup label="Open leads">
                                @foreach ($pickLeads as $l)
                                    <option value="lead:{{ $l->id }}">{{ $l->name }} · {{ $l->lead_no }}{{ $l->phone ? ' · '.$l->phone : '' }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </x-select>
                </div>
                <x-select label="Type" name="call_type" wire:model="call_type">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-select label="Assigned to" name="assigned_to" wire:model="assigned_to">
                    <option value="">Unassigned</option>
                    @foreach ($staff as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </x-select>
                <x-input :label="$mode === 'pending' ? 'Call on *' : 'Date *'" name="date" type="date" wire:model="date" />
                <x-input label="Time" name="time" type="time" wire:model="time" />
                @if ($mode === 'done')
                    <x-select label="How did it go?" name="outcome" wire:model="outcome">
                        <option value="">—</option>
                        @foreach ($outcomes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-input label="Next follow-up" name="next_date" type="date" wire:model="next_date" />
                @endif
                <div class="sm:col-span-2 lg:col-span-4">
                    <x-textarea :label="$mode === 'pending' ? 'What to talk about' : 'Notes'" name="notes" rows="2" wire:model="notes" />
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                <x-button type="submit">{{ $mode === 'pending' ? 'Schedule call' : 'Save call' }}</x-button>
            </div>
        </form>
    @endif

    {{-- Filters --------------------------------------------------------------- --}}
    <x-filter-panel>
        <div class="min-w-[200px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name, phone, lead/client no, notes…" />
        </div>
        <div>
            <x-select label="Show" name="when" wire:model.live="when">
                <option value="todo">To call now (overdue + today)</option>
                <option value="overdue">Overdue</option>
                <option value="today">Today</option>
                <option value="week">Next 7 days</option>
                <option value="month">Next 30 days</option>
                <option value="upcoming">All upcoming</option>
                <option value="history">History (done / missed)</option>
                <option value="all">Everything</option>
            </x-select>
        </div>
        <div class="min-w-[11rem]">
            <x-select label="With" name="with" wire:model.live="with">
                <option value="">Leads &amp; clients</option>
                <option value="lead">Leads</option>
                <option value="client">Clients</option>
            </x-select>
        </div>
        <div class="min-w-[11rem]">
            <x-select label="Type" name="type" wire:model.live="type">
                <option value="">Calls &amp; WhatsApp</option>
                <option value="all">All follow-ups</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }} only</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-select label="Assigned to" name="assigned" wire:model.live="assigned">
                <option value="">Anyone</option>
                <option value="me">Me</option>
                @foreach ($staff as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
    </x-filter-panel>

    @if ($focus)
        <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-ink-600">
            <span class="chip">Only {{ $focus->name }} · {{ $focus->lead_no ?? $focus->client_no }}</span>
            <button type="button" wire:click="$set('{{ $lead ? 'lead' : 'client' }}', null)" class="text-xs font-medium text-accent-600 hover:underline">show everyone</button>
        </div>
    @endif

    {{-- List ------------------------------------------------------------------- --}}
    @if ($calls->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                :title="in_array($when, ['todo', 'overdue', 'today']) ? 'No calls due — all caught up' : 'No calls found'"
                description="Schedule a call for a lead or client, or log one you just made — it shows up here, on their page and on the CRM overview."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Lead / client</th>
                    <th>Type</th>
                    <th>Notes</th>
                    <th>Assigned</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($calls as $call)
                    @php($pending = $call->status === \App\Enums\FollowupStatus::Pending)
                    @php($phone = $call->contactPhone())
                    <tr wire:key="call-{{ $call->id }}">
                        <td class="whitespace-nowrap">
                            <span class="block font-medium text-ink-900">{{ $call->date->format('d M Y') }}</span>
                            <span class="block text-xs text-ink-500">{{ $call->time ? \Illuminate\Support\Str::substr($call->time, 0, 5) : 'any time' }}</span>
                        </td>
                        <td class="max-w-[260px]">
                            <div class="flex items-center gap-2">
                                <a href="{{ $call->contactUrl() }}" wire:navigate class="truncate font-semibold text-accent-600 hover:underline">{{ $call->contactName() ?? '—' }}</a>
                                <span @class([
                                    'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                                    'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $call->client_id,
                                    'bg-ink-100 text-ink-600' => ! $call->client_id,
                                ])>{{ $call->contactKind() }}</span>
                            </div>
                            <span class="block truncate text-xs text-ink-500">{{ $call->contactNo() }} · {{ $phone ?: 'no phone' }}</span>
                        </td>
                        <td class="whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-700"><x-icon :name="$call->type->icon()" class="h-3.5 w-3.5 text-ink-400" />{{ $call->type->label() }}</span>
                        </td>
                        <td class="max-w-[280px]">
                            <span class="line-clamp-2 text-sm text-ink-600" title="{{ $call->notes }}">{{ $call->notes ?: '—' }}</span>
                        </td>
                        <td class="whitespace-nowrap text-sm">{{ $call->assignee?->name ?? '—' }}</td>
                        <td><x-call-due :call="$call" /></td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                @if ($pending)
                                    <x-call-actions :phone="$phone" />
                                @endif
                                @if ($callSvc->canManage(auth()->user(), $call))
                                    <x-dropdown>
                                        @if ($pending)
                                            <button type="button" wire:click="startComplete({{ $call->id }})" class="dropdown-item">Mark done…</button>
                                        @endif
                                        @if ($call->contactUrl())
                                            <a href="{{ $call->contactUrl() }}" wire:navigate class="dropdown-item">Open {{ strtolower($call->contactKind()) }}</a>
                                        @endif
                                        <button type="button" wire:click="deleteCall({{ $call->id }})" wire:confirm="Remove this call?" class="dropdown-item-danger">Remove</button>
                                    </x-dropdown>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if ($completing === $call->id)
                        <tr wire:key="complete-{{ $call->id }}">
                            <td colspan="7" class="!bg-ink-50/60">
                                <form wire:submit="complete" class="grid grid-cols-1 items-end gap-3 sm:grid-cols-4">
                                    <x-select label="How did it go? *" name="complete_outcome" wire:model="complete_outcome">
                                        @foreach ($outcomes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-select>
                                    <x-input label="Result" name="complete_result" wire:model="complete_result" placeholder="e.g. wants a revised quote" />
                                    <x-input label="Call again on" name="complete_next_date" type="date" wire:model="complete_next_date" />
                                    <div class="flex justify-end gap-2">
                                        <button type="button" wire:click="$set('completing', null)" class="btn-secondary">Cancel</button>
                                        <x-button type="submit">Save</x-button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-4">
            {{ $calls->links() }}
        </div>
    @endif
</div>
