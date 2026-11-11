<div class="panel p-5 sm:p-6">
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="section-title">Calls &amp; follow-ups</p>
            <p class="section-sub">
                {{ $scheduled->count() }} scheduled · {{ $history->count() }} logged ·
                <a href="{{ route('calls.index', [$lead ? 'lead' : 'client' => ($lead ?? $client)->id]) }}" wire:navigate class="font-medium text-accent-600 hover:underline">open in Calls</a>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-call-actions :phone="$phone" labelled />
            @if ($canEdit)
                <button type="button" wire:click="openForm('done')" class="btn-secondary !px-3.5 !py-2 text-xs">
                    <x-icon name="plus" class="h-3.5 w-3.5" /> Log call
                </button>
                <button type="button" wire:click="openForm('pending')" class="btn-secondary !px-3.5 !py-2 text-xs">
                    <x-icon name="clock" class="h-3.5 w-3.5" /> Schedule
                </button>
            @endif
        </div>
    </div>

    @unless ($phone)
        <p class="mb-4 rounded-2xl bg-ink-50 px-3 py-2 text-xs text-ink-500">No phone number on file — add one to call or WhatsApp in one tap.</p>
    @endunless

    {{-- Log / schedule form --------------------------------------------- --}}
    @if ($showForm)
        <form wire:submit="save" class="mb-5 rounded-2xl border border-ink-200/80 bg-ink-50/50 p-4 dark:border-white/[0.06]">
            <div class="mb-3 inline-flex rounded-full border border-ink-200/70 bg-surface p-1 text-xs font-medium">
                <button type="button" wire:click="$set('mode', 'done')" @class(['rounded-full px-3 py-1.5', 'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $mode === 'done', 'text-ink-500' => $mode !== 'done'])>Log a call I made</button>
                <button type="button" wire:click="$set('mode', 'pending')" @class(['rounded-full px-3 py-1.5', 'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $mode === 'pending', 'text-ink-500' => $mode !== 'pending'])>Schedule a call</button>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <x-select label="Type" name="type" wire:model="type">
                    @foreach (\App\Enums\FollowupType::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-input :label="$mode === 'pending' ? 'Call on *' : 'Date *'" name="date" type="date" wire:model="date" />
                <x-input label="Time" name="time" type="time" wire:model="time" />
                <x-select label="Assigned to" name="assigned_to" wire:model="assigned_to">
                    <option value="">Unassigned</option>
                    @foreach ($staff as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </x-select>
                @if ($mode === 'done')
                    <x-select label="How did it go?" name="outcome" wire:model="outcome">
                        <option value="">—</option>
                        @foreach (\App\Enums\CallOutcome::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-input label="Next follow-up" name="next_date" type="date" wire:model="next_date" />
                @endif
                <div @class(['sm:col-span-2', 'lg:col-span-2' => $mode === 'done', 'lg:col-span-4' => $mode === 'pending'])>
                    <x-textarea :label="$mode === 'pending' ? 'What to talk about' : 'Notes'" name="notes" rows="2" wire:model="notes" />
                </div>
            </div>
            @if ($mode === 'done')
                <p class="mt-2 text-[11px] text-ink-500">A next follow-up date books the next call automatically — it shows up in Calls and the CRM overview on that day.</p>
            @endif
            <div class="mt-3 flex justify-end gap-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                <x-button type="submit">{{ $mode === 'pending' ? 'Schedule call' : 'Save call' }}</x-button>
            </div>
        </form>
    @endif

    {{-- Scheduled ---------------------------------------------------------- --}}
    @if ($scheduled->isNotEmpty())
        <p class="mb-2 text-xs font-semibold text-ink-500">Scheduled</p>
        <ul class="mb-5 space-y-2">
            @foreach ($scheduled as $call)
                <li wire:key="sched-{{ $call->id }}" @class([
                    'rounded-2xl border p-3',
                    'border-danger-200 bg-danger-50/40 dark:border-danger-500/20 dark:bg-danger-500/5' => $call->date->lt($today),
                    'border-accent-500/30 bg-accent-500/5' => $call->date->isSameDay($today),
                    'border-ink-200/70 dark:border-white/[0.06]' => $call->date->gt($today),
                ])>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface text-ink-600 ring-1 ring-ink-200/70">
                            <x-icon :name="$call->type->icon()" class="h-3.5 w-3.5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink-900">
                                {{ $call->type->label() }} · {{ $call->date->format('D, d M Y') }}@if ($call->time) at {{ \Illuminate\Support\Str::substr($call->time, 0, 5) }}@endif
                            </p>
                            <p class="text-xs text-ink-500">
                                {{ $call->assignee?->name ?? 'Unassigned' }}
                                @if ($client && $call->lead_id) · from lead {{ $call->lead?->lead_no }} @endif
                            </p>
                            @if ($call->notes)
                                <p class="mt-1 whitespace-pre-line text-sm text-ink-600">{{ $call->notes }}</p>
                            @endif
                        </div>
                        <x-call-due :call="$call" />
                    </div>

                    @if ($canEdit)
                        @if ($completing === $call->id)
                            <form wire:submit="complete" class="mt-3 grid grid-cols-1 gap-3 border-t border-ink-200/70 pt-3 sm:grid-cols-3 dark:border-white/[0.06]">
                                <x-select label="How did it go? *" name="complete_outcome" wire:model="complete_outcome">
                                    @foreach (\App\Enums\CallOutcome::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-select>
                                <x-input label="Call again on" name="complete_next_date" type="date" wire:model="complete_next_date" />
                                <x-input label="Result" name="complete_result" wire:model="complete_result" placeholder="e.g. wants a revised quote" />
                                <div class="flex justify-end gap-2 sm:col-span-3">
                                    <button type="button" wire:click="$set('completing', null)" class="btn-secondary">Cancel</button>
                                    <x-button type="submit">Save</x-button>
                                </div>
                            </form>
                        @else
                            <div class="mt-2 flex justify-end gap-3 text-xs font-medium">
                                <button type="button" wire:click="deleteCall({{ $call->id }})" wire:confirm="Remove this scheduled call?" class="text-ink-500 hover:text-danger-600">Remove</button>
                                <button type="button" wire:click="startComplete({{ $call->id }})" class="inline-flex items-center gap-1 text-accent-600 hover:underline">
                                    <x-icon name="check" class="h-3.5 w-3.5" /> Mark done
                                </button>
                            </div>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{-- History ------------------------------------------------------------- --}}
    <p class="mb-2 text-xs font-semibold text-ink-500">History</p>
    @if ($history->isEmpty())
        <p class="rounded-2xl border border-dashed border-ink-200 py-6 text-center text-sm text-ink-500">No calls logged yet.</p>
    @else
        <ol class="relative space-y-4 border-l border-ink-200/80 pl-5 dark:border-white/[0.08]">
            @foreach ($history as $call)
                <li wire:key="hist-{{ $call->id }}" class="relative">
                    <span class="absolute -left-[31px] top-0 flex h-5 w-5 items-center justify-center rounded-full bg-surface ring-1 ring-ink-200">
                        <x-icon :name="$call->type->icon()" class="h-3 w-3 text-ink-500" />
                    </span>
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <p class="text-sm font-medium text-ink-900">{{ $call->type->label() }} · {{ $call->date->format('d M Y') }}@if ($call->time) <span class="text-ink-500">{{ \Illuminate\Support\Str::substr($call->time, 0, 5) }}</span>@endif</p>
                        <x-call-due :call="$call" />
                    </div>
                    <p class="text-xs text-ink-500">
                        {{ $call->assignee?->name ?? 'Unassigned' }}
                        @if ($client && $call->lead_id) · from lead {{ $call->lead?->lead_no }} @endif
                        @if ($call->next_followup_date) · next {{ $call->next_followup_date->format('d M Y') }} @endif
                    </p>
                    @if ($call->notes)
                        <p class="mt-1 whitespace-pre-line text-sm text-ink-600">{{ $call->notes }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
