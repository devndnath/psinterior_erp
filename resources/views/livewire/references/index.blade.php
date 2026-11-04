<div>
    <x-page-header title="References" description="Who brings us our clients — every referrer, with the clients and leads they introduced.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['References' => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    {{-- At a glance ------------------------------------------------------ --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4">
        <div class="panel p-4 sm:p-5">
            <p class="text-xs font-medium text-ink-500">Referrers</p>
            <p class="mt-2 text-2xl font-bold tracking-tight tabular-nums text-ink-900">{{ number_format($summary['referrers']) }}</p>
            <p class="mt-1 truncate text-[11px] text-ink-500">people who introduced clients</p>
        </div>
        <div class="panel p-4 sm:p-5">
            <p class="text-xs font-medium text-ink-500">Referred clients</p>
            <p class="mt-2 text-2xl font-bold tracking-tight tabular-nums text-ink-900">{{ number_format($summary['clients']) }}</p>
            <p class="mt-1 truncate text-[11px] text-ink-500">{{ $summary['share'] }}% of all clients · {{ number_format($summary['leads']) }} open {{ \Illuminate\Support\Str::plural('lead', $summary['leads']) }}</p>
        </div>
    </div>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Referrer, phone, or a client they referred…" />
        </div>
        <div>
            <x-select label="Referrer type" name="type" wire:model.live="type">
                <option value="">All</option>
                <option value="client">Existing clients</option>
                <option value="employee">Staff</option>
                <option value="other">Other people</option>
            </x-select>
        </div>
    </div>

    @if ($referrers->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No references yet"
                description="Record who introduced a lead or client in the Reference section of the lead or client form."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Referrer</th>
                    <th>Type</th>
                    <th>Referred clients &amp; leads</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($referrers as $r)
                    <tr wire:key="ref-{{ $loop->index }}-{{ md5($r['name'].$r['type']) }}">
                        <td class="max-w-[220px]">
                            @if ($r['href'])
                                <a href="{{ $r['href'] }}" wire:navigate class="block truncate font-semibold text-accent-600 hover:underline">{{ $r['name'] }}</a>
                            @else
                                <span class="block truncate font-semibold text-ink-900">{{ $r['name'] }}</span>
                            @endif
                            <span class="block truncate text-xs text-ink-500">{{ $r['phone'] ?: 'no phone' }}</span>
                        </td>
                        <td>
                            <span @class([
                                'chip',
                                '!border-success-500/30 !bg-success-50 !text-success-700 dark:!bg-success-500/10 dark:!text-success-400' => $r['type'] === 'client',
                                '!border-accent-500/40 !bg-accent-500/10 !text-accent-700 dark:!text-accent-400' => $r['type'] === 'employee',
                            ])>{{ $r['kind'] }}</span>
                        </td>
                        <td>
                            <div class="flex max-w-xl flex-wrap gap-1.5">
                                @foreach ($r['clients'] as $c)
                                    <a href="{{ route('clients.show', $c) }}" wire:navigate class="inline-flex items-center gap-1 rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-800 hover:bg-ink-200">
                                        <x-icon name="users" class="h-3 w-3 text-ink-500" />{{ $c->name }}
                                    </a>
                                @endforeach
                                @foreach ($r['leads'] as $l)
                                    <a href="{{ route('leads.show', $l) }}" wire:navigate class="inline-flex items-center gap-1 rounded-full border border-dashed border-ink-300 px-2.5 py-1 text-xs font-medium text-ink-600 hover:border-ink-400" title="Lead · {{ $l->status->label() }}">
                                        <x-icon name="funnel" class="h-3 w-3 text-ink-400" />{{ $l->name }}
                                    </a>
                                @endforeach
                            </div>
                            <span class="mt-1 block text-[11px] text-ink-500">
                                {{ $r['clients']->count() }} {{ \Illuminate\Support\Str::plural('client', $r['clients']->count()) }}@if ($r['leads']->count()) · {{ $r['leads']->count() }} open {{ \Illuminate\Support\Str::plural('lead', $r['leads']->count()) }}@endif
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</div>
