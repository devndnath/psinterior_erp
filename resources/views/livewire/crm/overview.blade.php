<div>
    {{--
        CRM overview, opened from the dashboard's CRM card: calls, client
        visits, meetings, the lead pipeline, project stages, client dues
        and new leads/clients this year — the sections of the firm's
        previous CRM dashboard, in the soft-canvas style. Every figure
        comes from App\Livewire\Crm\Overview; a section is left out when
        the user can't see its module.

        Bucket colours: today = emerald, overdue = red, next 7 days =
        blue, next 30 days = charcoal. Each card prints its number, so
        colour never carries meaning alone.
    --}}
    @php
        $tones = [
            'today' => ['label' => 'Today', 'icon' => 'calendar', 'chip' => 'bg-success-600 text-white', 'ring' => ''],
            'overdue' => ['label' => 'Overdue', 'icon' => 'alert-triangle', 'chip' => 'bg-danger-600 text-white', 'ring' => 'ring-2 ring-danger-500/30'],
            'week' => ['label' => 'Next 7 days', 'icon' => 'clock', 'chip' => 'bg-[#2a78d6] text-white dark:bg-[#3987e5]', 'ring' => ''],
            'month' => ['label' => 'Next 30 days', 'icon' => 'calendar', 'chip' => 'bg-charcoal text-accent-400 dark:bg-white/10', 'ring' => ''],
        ];
        $user = auth()->user();
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <x-breadcrumbs :items="['CRM Overview' => null]" class="mb-2" />
            <h1 class="page-title">CRM Overview</h1>
            <p class="mt-1 text-sm text-ink-500">Calls, visits, meetings and the pipeline for {{ $today->format('l, j F Y') }}.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('create_site_visits')
                <a href="{{ route('site-visits.create') }}" wire:navigate class="btn-secondary">+ Site Visit</a>
            @endcan
            @can('create_leads')
                <a href="{{ route('leads.create') }}" wire:navigate class="btn-primary">+ New Lead</a>
            @endcan
        </div>
    </div>

    {{-- Lead KPIs --------------------------------------------------- --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Open leads', 'value' => number_format($leads['open']), 'icon' => 'funnel', 'href' => route('leads.index')],
            ['label' => 'New this month', 'value' => number_format($leads['newThisMonth']), 'icon' => 'arrow-up-right', 'href' => route('leads.index')],
            ['label' => 'Won this month', 'value' => number_format($leads['wonThisMonth']), 'icon' => 'check', 'href' => route('leads.index', ['status' => 'won'])],
            ['label' => 'Win rate', 'value' => $leads['winRate'] !== null ? $leads['winRate'].'%' : '—', 'icon' => 'gauge', 'href' => null, 'hint' => 'won ÷ (won + lost)'],
        ] as $k)
            <a @if ($k['href']) href="{{ $k['href'] }}" wire:navigate @endif class="panel group flex items-center gap-4 p-4 transition hover:-translate-y-0.5 sm:p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-500/15 text-accent-700 dark:text-accent-400">
                    <x-icon :name="$k['icon']" class="h-5 w-5" />
                </span>
                <span class="min-w-0">
                    <span class="block text-2xl font-bold tracking-tight tabular-nums text-ink-900">{{ $k['value'] }}</span>
                    <span class="block truncate text-xs text-ink-500">{{ $k['label'] }}@isset($k['hint']) · {{ $k['hint'] }}@endisset</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Call overview ------------------------------------------------ --}}
    <div class="panel mb-5 p-5 sm:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="section-title">Call Overview</p>
                <p class="section-sub">Pending call &amp; WhatsApp follow-ups on open leads</p>
            </div>
            <a href="{{ route('leads.index') }}" wire:navigate class="text-sm font-medium text-accent-600 hover:underline">All leads</a>
        </div>
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
            <div class="grid grid-cols-2 content-start gap-3 xl:col-span-5">
                @foreach ($calls['counts'] as $key => $n)
                    @php $t = $tones[$key]; @endphp
                    <div @class(['rounded-3xl border border-ink-200/70 bg-surface p-4 dark:border-white/[0.06]', $t['ring'] => $key === 'overdue' && $n > 0])>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $t['chip'] }}">
                            <x-icon :name="$t['icon']" class="h-4 w-4" />
                        </span>
                        <p @class(['mt-3 text-3xl font-bold tracking-tight tabular-nums', 'text-danger-600' => $key === 'overdue' && $n > 0, 'text-ink-900' => ! ($key === 'overdue' && $n > 0)])>{{ number_format($n) }}</p>
                        <p class="text-xs font-medium text-ink-500">{{ $t['label'] }}{{ $key === 'today' ? "'s calls" : ' calls' }}</p>
                    </div>
                @endforeach
            </div>
            <div class="xl:col-span-7">
                <p class="mb-2 text-xs font-semibold text-ink-500">Calls to make now — overdue and today</p>
                @forelse ($calls['due'] as $f)
                    @php $late = $f->date->lt($today); @endphp
                    <a href="{{ route('leads.show', $f->lead_id) }}" wire:navigate class="flex items-center gap-3 border-b border-ink-100 py-2.5 last:border-0 hover:bg-ink-50/60">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-ink-100 text-xs font-bold text-ink-700">
                            {{ \Illuminate\Support\Str::of($f->lead?->name ?? '?')->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-ink-900">{{ $f->lead?->name }}</span>
                            <span class="block truncate text-xs text-ink-500">{{ $f->type->label() }} · {{ $f->lead?->phone ?? 'no phone' }}{{ $f->assignee ? ' · '.$f->assignee->name : '' }}</span>
                        </span>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                            'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300' => $late,
                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => ! $late,
                        ])>
                            {{ $late ? $f->date->diffForHumans($today, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]).' late' : 'Today' }}
                        </span>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-ink-200 py-8 text-center text-sm text-ink-500">No calls due — you're all caught up.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Client visits + meetings ---------------------------------------- --}}
    @if ($visits || $meetings)
        <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
            @foreach (array_filter(['visits' => $visits, 'meetings' => $meetings]) as $kind => $block)
                @php $isVisits = $kind === 'visits'; @endphp
                <div class="panel flex flex-col p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <div>
                            <p class="section-title">{{ $isVisits ? 'Client Visit Overview' : 'Meeting Overview' }}</p>
                            <p class="section-sub">{{ $isVisits ? 'Site visits scheduled' : 'Client & project meetings' }}</p>
                        </div>
                        <a href="{{ route($isVisits ? 'site-visits.index' : 'meetings.index') }}" wire:navigate class="text-sm font-medium text-accent-600 hover:underline">View all</a>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($block['counts'] as $key => $n)
                            @php $t = $tones[$key]; @endphp
                            <div class="rounded-3xl border border-ink-200/70 bg-surface p-3.5 dark:border-white/[0.06] sm:p-4">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $t['chip'] }}">
                                    <x-icon :name="$t['icon']" class="h-4 w-4" />
                                </span>
                                <p class="mt-2.5 text-2xl font-bold tracking-tight tabular-nums text-ink-900">{{ number_format($n) }}</p>
                                <p class="truncate text-[11px] font-medium text-ink-500">{{ $t['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="mb-1 mt-5 text-xs font-semibold text-ink-500">Coming up</p>
                    <div class="flex-1">
                        @forelse ($block['next'] as $item)
                            @php
                                $date = $isVisits ? $item->visit_date : $item->date;
                                $href = $isVisits ? route('site-visits.show', $item) : route('meetings.show', $item);
                                $title = $isVisits ? ($item->client?->name ?? $item->sv_no) : $item->type->label().($item->project ? ' — '.$item->project->project_code : '');
                                $sub = $isVisits ? ($item->site_address ?: $item->purpose) : ($item->location ?: \Illuminate\Support\Str::limit((string) $item->agenda, 60));
                            @endphp
                            <a href="{{ $href }}" wire:navigate class="flex items-center gap-3 border-b border-ink-100 py-2.5 last:border-0 hover:bg-ink-50/60">
                                <span class="flex w-11 shrink-0 flex-col items-center rounded-2xl bg-ink-100 py-1 leading-tight">
                                    <span class="text-[10px] font-semibold uppercase text-ink-500">{{ $date->format('M') }}</span>
                                    <span class="text-base font-bold tabular-nums text-ink-900">{{ $date->format('j') }}</span>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-ink-900">{{ $title }}</span>
                                    <span class="block truncate text-xs text-ink-500">{{ $sub ?: '—' }}</span>
                                </span>
                                <span class="shrink-0 text-[11px] font-medium text-ink-500">
                                    @if ($date->isSameDay($today))
                                        <span class="rounded-full bg-success-50 px-2 py-0.5 font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">Today</span>
                                    @else
                                        {{ $date->format('D') }}{{ ! $isVisits && $item->time ? ' · '.$item->time : '' }}
                                    @endif
                                </span>
                            </a>
                        @empty
                            <p class="rounded-2xl border border-dashed border-ink-200 py-6 text-center text-sm text-ink-500">Nothing scheduled.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pipeline + projects ------------------------------------------- --}}
    <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="panel p-5 sm:p-6">
            <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                    <p class="section-title">Lead Pipeline</p>
                    <p class="section-sub">Every lead by stage</p>
                </div>
                @can('view_reports')
                    <a href="{{ route('reports.sales-funnel') }}" wire:navigate class="text-sm font-medium text-accent-600 hover:underline">Sales funnel</a>
                @endcan
            </div>
            <x-status-bars :rows="$pipeline" unit="leads" empty="No leads yet." compact />
        </div>
        @if ($projects)
            <div class="panel p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <div>
                        <p class="section-title">Projects</p>
                        <p class="section-sub">{{ number_format($projects['active']) }} active · by stage</p>
                    </div>
                    <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-medium text-accent-600 hover:underline">All projects</a>
                </div>
                <x-status-bars :rows="$projects['rows']" unit="projects" empty="No projects yet." compact />
            </div>
        @endif
    </div>

    {{-- Dues + clients this year --------------------------------------- --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
        @if ($dues)
            <div class="panel flex flex-col p-2 lg:col-span-5">
                <div class="rounded-[22px] bg-charcoal px-5 py-5 text-white dark:bg-white/[0.06]">
                    <p class="text-sm font-medium text-white/75">Client dues</p>
                    <p class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-sm font-semibold text-white/60">{{ $currency }}</span>
                        <span class="text-3xl font-bold tracking-tight tabular-nums text-accent-400">{{ number_format($dues['total'], 2) }}</span>
                    </p>
                    <p class="mt-1 text-xs text-white/65">
                        @if ($dues['overdueCount'])
                            <span class="font-semibold text-danger-300">{{ number_format($dues['overdue'], 2) }} overdue</span> on {{ $dues['overdueCount'] }} {{ \Illuminate\Support\Str::plural('invoice', $dues['overdueCount']) }}
                        @else
                            Nothing overdue
                        @endif
                    </p>
                </div>
                <ul class="flex-1 divide-y divide-ink-100 px-4 py-2">
                    @forelse ($dues['top'] as $inv)
                        <li>
                            <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="flex items-center justify-between gap-3 py-2.5 text-sm hover:bg-ink-50/60">
                                <span class="min-w-0">
                                    <span class="block truncate font-semibold text-ink-900">{{ $inv->client?->name ?? '—' }}</span>
                                    <span class="block text-xs text-ink-500">{{ $inv->invoice_no }}{{ $inv->due_date ? ' · due '.$inv->due_date->format('j M') : '' }}</span>
                                </span>
                                <span @class(['shrink-0 font-bold tabular-nums', 'text-danger-600' => $inv->due_date && $inv->due_date->lt($today), 'text-ink-900' => ! ($inv->due_date && $inv->due_date->lt($today))])>{{ number_format($inv->outstandingAmount(), 2) }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="py-8 text-center text-sm text-ink-500">No open invoices.</li>
                    @endforelse
                </ul>
            </div>
        @endif

        <div @class(['panel flex flex-col p-5 sm:p-6', 'lg:col-span-7' => $dues, 'lg:col-span-12' => ! $dues])
            x-data="{
                init() {
                    const t = window.psChartTheme();
                    const datasets = [{ label: 'New leads', data: @js($chart['leads']), backgroundColor: document.documentElement.classList.contains('dark') ? '#3987e5' : '#2a78d6', borderRadius: 6, maxBarThickness: 18 }];
                    @if ($chart['clients'] !== null)
                        datasets.push({ label: 'New clients', data: @js($chart['clients']), backgroundColor: t.income, borderRadius: 6, maxBarThickness: 18 });
                    @endif
                    if (! window.Chart) return;
                    new window.Chart(this.$refs.clients, {
                        type: 'bar',
                        data: { labels: @js($chart['labels']), datasets },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: { backgroundColor: t.tooltipBg, titleColor: t.tooltipText, bodyColor: t.tooltipText, padding: 10, cornerRadius: 12, boxPadding: 4, usePointStyle: true },
                            },
                            scales: {
                                x: { grid: { display: false }, border: { display: false }, ticks: { color: t.text } },
                                y: { beginAtZero: true, grid: { color: t.grid }, border: { display: false }, ticks: { color: t.text, precision: 0, maxTicksLimit: 5 } },
                            },
                        },
                    });
                },
            }">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="section-title">Clients &amp; Leads {{ $year }}</p>
                    <p class="section-sub">
                        {{ number_format(array_sum($chart['leads'])) }} new leads
                        @if ($chart['clients'] !== null)
                            · {{ number_format(array_sum($chart['clients'])) }} new clients
                        @endif
                        this year
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs text-ink-600">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#2a78d6] dark:bg-[#3987e5]"></span>New leads</span>
                    @if ($chart['clients'] !== null)
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#059669] dark:bg-[#12A372]"></span>New clients</span>
                    @endif
                </div>
            </div>
            <div class="relative min-h-64 flex-1" wire:ignore>
                <canvas x-ref="clients" aria-label="New leads and new clients by month, {{ $year }}" role="img"></canvas>
            </div>
        </div>
    </div>
</div>
