<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-ink-900">Calendar</h1>
            <p class="mt-1 text-sm text-ink-500">
                Milestones, tasks, site visits, meetings, invoice due dates, quotation expiry and warranty expiry — everything with a date, in one place.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($projects->isNotEmpty())
                <select wire:model.live="project" class="rounded-lg border-ink-300 text-sm shadow-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">All projects</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->project_code }} — {{ $p->name }}</option>
                    @endforeach
                </select>
            @endif

            <div class="flex items-center gap-1 rounded-lg border border-ink-200 bg-white p-1">
                <button type="button" wire:click="previousMonth" class="rounded-md p-1.5 text-ink-500 hover:bg-ink-100" aria-label="Previous month">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </button>
                <span class="min-w-[9rem] text-center text-sm font-semibold text-ink-900">{{ $monthStart->format('F Y') }}</span>
                <button type="button" wire:click="nextMonth" class="rounded-md p-1.5 text-ink-500 hover:bg-ink-100" aria-label="Next month">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </button>
            </div>

            <button type="button" wire:click="goToToday" class="btn-secondary">Today</button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mb-4 flex flex-wrap gap-3 text-xs text-ink-500">
        @foreach ([
            ['label' => 'Milestone', 'color' => 'purple'],
            ['label' => 'Task', 'color' => 'blue'],
            ['label' => 'Site Visit', 'color' => 'indigo'],
            ['label' => 'Meeting', 'color' => 'green'],
            ['label' => 'Invoice Due', 'color' => 'red'],
            ['label' => 'Quotation Expiry', 'color' => 'yellow'],
            ['label' => 'Warranty Expiry', 'color' => 'gray'],
        ] as $legendItem)
            <span class="inline-flex items-center gap-1.5">
                <x-status-badge :label="$legendItem['label']" :color="$legendItem['color']" />
            </span>
        @endforeach
    </div>

    @php
        $firstWeekday = (int) $monthStart->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $leadingBlanks = $firstWeekday % 7; // Sunday-first grid: Sun=0 blanks, Mon=1, ... Sat=6
        $daysInMonth = (int) $monthStart->daysInMonth;
        $totalCells = $leadingBlanks + $daysInMonth;
        $trailingBlanks = (7 - ($totalCells % 7)) % 7;
        $today = now()->toDateString();
    @endphp

    <div class="card overflow-hidden p-0">
        <div class="grid grid-cols-7 border-b border-ink-100 bg-ink-50 text-xs font-semibold uppercase tracking-wide text-ink-500">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow)
                <div class="px-3 py-2 text-center">{{ $dow }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @for ($i = 0; $i < $leadingBlanks; $i++)
                <div class="min-h-[7rem] border-b border-r border-ink-100 bg-ink-50/40"></div>
            @endfor

            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $cellDate = $monthStart->day($day)->toDateString();
                    $dayEvents = $eventsByDate->get($cellDate, collect());
                    $isToday = $cellDate === $today;
                @endphp
                <div class="min-h-[7rem] border-b border-r border-ink-100 p-2 {{ $isToday ? 'bg-accent-50' : '' }}" wire:key="day-{{ $cellDate }}">
                    <p class="mb-1 text-xs font-semibold {{ $isToday ? 'text-accent-700' : 'text-ink-500' }}">
                        {{ $day }}
                    </p>
                    <div class="space-y-1">
                        @foreach ($dayEvents->take(4) as $event)
                            @if ($event['url'])
                                <a href="{{ $event['url'] }}" wire:navigate class="block truncate rounded px-1.5 py-0.5 text-[11px] font-medium leading-tight hover:underline {{ match ($event['color']) {
                                    'purple' => 'bg-purple-100 text-purple-700',
                                    'blue' => 'bg-accent-100 text-accent-700',
                                    'indigo' => 'bg-indigo-100 text-indigo-700',
                                    'green' => 'bg-success-100 text-success-700',
                                    'red' => 'bg-danger-100 text-danger-700',
                                    'yellow' => 'bg-warning-100 text-warning-800',
                                    default => 'bg-ink-100 text-ink-700',
                                } }}" title="{{ $event['type'] }}: {{ $event['title'] }}{{ $event['subtitle'] ? ' ('.$event['subtitle'].')' : '' }}">
                                    {{ $event['title'] }}
                                </a>
                            @endif
                        @endforeach
                        @if ($dayEvents->count() > 4)
                            <p class="px-1.5 text-[11px] text-ink-400">+{{ $dayEvents->count() - 4 }} more</p>
                        @endif
                    </div>
                </div>
            @endfor

            @for ($i = 0; $i < $trailingBlanks; $i++)
                <div class="min-h-[7rem] border-b border-r border-ink-100 bg-ink-50/40"></div>
            @endfor
        </div>
    </div>
</div>
