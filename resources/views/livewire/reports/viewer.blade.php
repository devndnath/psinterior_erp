<div>
    {{--
        One page for every report in App\Reports\Registry. The report
        decides its filters, columns and rows; this view draws the filter
        bar, summary cards, the table with its totals row, and the CSV /
        print actions, so every report reads the same way.
    --}}
    @php
        $columns = $def->columns();
        $isNum = fn ($t) => in_array($t, ['money', 'int', 'qty', 'pct'], true);
        $fmt = function ($v, $type) {
            if ($v === null || $v === '') return '—';
            return match ($type) {
                'money' => number_format((float) $v, 2),
                'int' => number_format((int) $v),
                'qty' => rtrim(rtrim(number_format((float) $v, 2), '0'), '.'),
                'pct' => rtrim(rtrim(number_format((float) $v, 1), '0'), '.').'%',
                'date' => \Carbon\CarbonImmutable::parse($v)->format('d M Y'),
                default => $v,
            };
        };
        $presets = ['today' => 'Today', 'week' => 'This week', 'month' => 'This month', 'last-month' => 'Last month', 'year' => 'This year', 'all' => 'All time'];
        $today = \Carbon\CarbonImmutable::today();
        $activePreset = collect($presets)->keys()->first(function ($k) use ($today) {
            [$a, $b] = match ($k) {
                'today' => [$today, $today],
                'week' => [$today->startOfWeek(), $today->endOfWeek()],
                'month' => [$today->startOfMonth(), $today->endOfMonth()],
                'last-month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
                'year' => [$today->startOfYear(), $today->endOfYear()],
                default => [null, null],
            };
            return $this->from === ($a?->toDateString() ?? '') && $this->to === ($b?->toDateString() ?? '');
        });
    @endphp

    <x-page-header :title="$def::title()" :description="$def::description()">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Reports' => route('reports.index'), $groupLabel => null, $def::title() => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <x-button variant="secondary" class="no-print" wire:click="exportCsv">Export CSV</x-button>
            <x-button variant="secondary" class="no-print" onclick="window.print()">Print</x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filters ------------------------------------------------------- --}}
    <div class="panel no-print mb-4 space-y-4 p-4 sm:p-5">
        @if (in_array('dates', $want))
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-wrap gap-1 rounded-full border border-ink-200 bg-surface p-1">
                    @foreach ($presets as $key => $label)
                        <button type="button" wire:click="applyRange('{{ $key }}')" @class([
                            'rounded-full px-3.5 py-1.5 text-sm font-medium transition',
                            'bg-charcoal text-white dark:bg-accent-500 dark:text-charcoal' => $activePreset === $key,
                            'text-ink-600 hover:text-ink-900' => $activePreset !== $key,
                        ])>{{ $label }}</button>
                    @endforeach
                </div>
                <x-input label="From" name="from" type="date" wire:model.live="from" class="w-40" />
                <x-input label="To" name="to" type="date" wire:model.live="to" class="w-40" />
            </div>
        @endif
        @php $selects = array_values(array_intersect(['project', 'client', 'vendor', 'employee', 'user', 'category', 'method'], $want)); @endphp
        @if ($selects)
            <div class="flex flex-wrap items-end gap-3">
                @foreach ($selects as $s)
                    <div class="min-w-[180px] flex-1 sm:max-w-xs">
                        <x-select :label="['project' => 'Project', 'client' => 'Client', 'vendor' => 'Vendor', 'employee' => 'Employee', 'user' => 'Staff member', 'category' => 'Category', 'method' => 'Method'][$s]" :name="$s" wire:model.live="{{ $s }}">
                            <option value="">All</option>
                            @foreach ($options[$s] as $value => $opt)
                                @if ($s === 'method')
                                    <option value="{{ $value }}">{{ $opt }}</option>
                                @elseif ($s === 'project')
                                    <option value="{{ $opt->id }}">{{ $opt->project_code }} — {{ $opt->name }}</option>
                                @elseif ($s === 'vendor')
                                    <option value="{{ $opt->id }}">{{ $opt->company_name }}</option>
                                @else
                                    <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                @endif
                            @endforeach
                        </x-select>
                    </div>
                @endforeach
                <x-button variant="secondary" wire:click="resetFilters">Reset</x-button>
            </div>
        @endif
    </div>

    {{-- Print header (screen hides it). --}}
    <div class="mb-4 hidden print:block">
        <p class="text-sm text-ink-500">{{ \App\Models\Setting::get('company.name', config('app.name')) }} · {{ $filters->rangeLabel() }} · printed {{ now()->format('d M Y H:i') }}</p>
    </div>

    {{-- Summary ------------------------------------------------------- --}}
    @if ($summary)
        <div @class(['mb-4 grid grid-cols-2 gap-3 sm:gap-4', 'xl:grid-cols-4' => count($summary) >= 4, 'xl:grid-cols-3' => count($summary) === 3])>
            @foreach ($summary as $card)
                @php $tone = $card['tone'] ?? 'plain'; $dark = in_array($tone, ['dark', 'red'], true); @endphp
                <div @class([
                    'rounded-3xl p-4 sm:p-5',
                    'bg-charcoal text-white dark:bg-white/[0.06]' => $tone === 'dark',
                    'bg-danger-600 text-white' => $tone === 'red',
                    'panel' => ! $dark,
                ])>
                    <p @class(['flex items-center gap-1.5 text-xs font-medium', 'text-white/70' => $dark, 'text-ink-500' => ! $dark])>
                        @if ($tone === 'green')<span class="h-2 w-2 rounded-full bg-[#059669] dark:bg-[#12A372]"></span>@endif
                        @if ($tone === 'gold')<span class="h-2 w-2 rounded-full bg-[#C68F00] dark:bg-[#BD8A00]"></span>@endif
                        {{ $card['label'] }}
                    </p>
                    <p class="mt-2 flex items-baseline gap-1 truncate">
                        @if ($card['type'] === 'money')
                            <span @class(['text-xs font-semibold', 'text-white/60' => $dark, 'text-ink-400' => ! $dark])>{{ $currency }}</span>
                        @endif
                        <span @class(['text-xl font-bold tracking-tight tabular-nums sm:text-2xl', 'text-accent-400' => $tone === 'dark', 'text-ink-900' => ! $dark])>{{ $fmt($card['value'], $card['type']) }}</span>
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($def->note())
        <p class="mb-4 flex items-start gap-2 px-1 text-xs text-ink-500">
            <x-icon name="list" class="mt-0.5 h-3.5 w-3.5 shrink-0" /> {{ $def->note() }}
        </p>
    @endif

    {{-- Table --------------------------------------------------------- --}}
    @if ($rows->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state title="Nothing to show" description="No records match these filters{{ in_array('dates', $want) ? ' for '.$filters->rangeLabel() : '' }}." />
        </div>
    @else
        <x-table class="report-table">
            <thead>
                <tr>
                    @foreach ($columns as $key => $col)
                        <th @class(['whitespace-nowrap', '!text-right' => $isNum($col['type'])])>{{ $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr wire:key="r-{{ $loop->index }}">
                        @foreach ($columns as $key => $col)
                            @php $v = $row[$key] ?? null; @endphp
                            @if (is_array($v))
                                <td class="max-w-[260px]">
                                    @if ($v['href'])
                                        <a href="{{ $v['href'] }}" wire:navigate class="block truncate font-medium text-accent-600 hover:underline">{{ $v['text'] }}</a>
                                    @else
                                        <span class="block truncate font-medium text-ink-900">{{ $v['text'] }}</span>
                                    @endif
                                    @if ($v['sub'])
                                        <span class="block truncate text-xs text-ink-500">{{ $v['sub'] }}</span>
                                    @endif
                                </td>
                            @else
                                <td @class([
                                    'whitespace-nowrap text-right tabular-nums' => $isNum($col['type']),
                                    'whitespace-nowrap text-ink-500' => $col['type'] === 'date',
                                    'max-w-[280px] truncate' => $col['type'] === 'text',
                                    'text-danger-600' => $col['type'] === 'money' && is_numeric($v) && $v < 0,
                                ]) @if ($col['type'] === 'text') title="{{ $v }}" @endif>{{ $fmt($v, $col['type']) }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if ($totals)
                <tfoot>
                    <tr class="border-t-2 border-ink-200 bg-ink-50/70 font-semibold text-ink-900">
                        @foreach ($columns as $key => $col)
                            <td @class(['px-5 py-3.5 first:pl-6 last:pr-6', 'whitespace-nowrap text-right tabular-nums' => $isNum($col['type']), 'text-danger-600' => array_key_exists($key, $totals) && $col['type'] === 'money' && $totals[$key] < 0])>
                                @if (array_key_exists($key, $totals))
                                    {{ $fmt($totals[$key], $col['type']) }}
                                @elseif ($loop->first)
                                    Total · {{ number_format($rows->count()) }} {{ \Illuminate\Support\Str::plural('row', $rows->count()) }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </x-table>
    @endif
</div>
