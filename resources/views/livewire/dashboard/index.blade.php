<div>
    {{--
        Soft-canvas pass: the overview modeled on the reference "Sales
        Performance" layout, in the PS Solution gold + charcoal —
        overlapping figure circles for the year, a revenue trend line,
        concentric health rings, status bars, then month-over-month KPI
        cards, the largest open invoices and an operations snapshot.
        Every number comes from App\Livewire\Dashboard\Index (real rows;
        null = the user can't see that module, or there's no data — the
        view says so rather than inventing a value).

        Chart colors (validated as a set with the dataviz palette script,
        light on #FFFFFF and dark on #1B1D19): emerald #059669 / #12A372,
        gold #C68F00 / #BD8A00, blue #2a78d6 / #3987e5. Gold is under 3:1
        on white, so every mark that uses it has a printed value beside it.
    --}}
    @php
        $firstName = \Illuminate\Support\Str::of(\Illuminate\Support\Facades\Auth::user()->name)->before(' ');
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        // Splits 1234.5 into ['1,234', '50'] so the decimals can be set smaller.
        $money = function ($v) {
            [$int, $dec] = explode('.', number_format((float) $v, 2));

            return [$int, $dec];
        };
        // Compact figure for the circles: 1,234,567 -> 1.23M.
        $compact = function ($v) {
            $a = abs((float) $v);
            $s = match (true) {
                $a >= 1_000_000_000 => rtrim(rtrim(number_format($a / 1_000_000_000, 2), '0'), '.').'B',
                $a >= 1_000_000 => rtrim(rtrim(number_format($a / 1_000_000, 2), '0'), '.').'M',
                $a >= 10_000 => rtrim(rtrim(number_format($a / 1_000, 1), '0'), '.').'K',
                default => number_format($a),
            };

            return ($v < 0 ? '−' : '').$s;
        };
        $pctText = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format($v, 1), '0'), '.').'%';

        $hasFinance = $income || $expenses || $invoiced || $receivables;
        $canReports = \Illuminate\Support\Facades\Auth::user()->can('view_reports');
    @endphp

    {{-- Heading ----------------------------------------------------------- --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Business Performance</h1>
            <p class="mt-1 text-sm text-ink-500">{{ $greeting }}, {{ $firstName }} — here's how the business is doing on {{ now()->format('l, j F Y') }}.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="chip py-2"><span class="h-2 w-2 rounded-full bg-accent-500"></span>{{ $monthLabel }}</span>
            @if ($canReports)
                <a href="{{ route('reports.index') }}" wire:navigate class="btn-primary py-2">View reports</a>
            @endif
        </div>
    </div>

    @if ($totalLeads === null && $totalClients === null && $upcomingSiteVisits === null && $activeProjects === null && $designsAwaitingDecision === null && $quotationsAwaitingDecision === null && $changeOrdersPending === null && $outstandingInvoicesCount === null && $expensesThisMonth === null && $lowStockProducts === null && $pendingRequisitions === null && $pendingPurchaseOrders === null && $pendingLeaveRequests === null && $payrollDue === null && $openSnags === null && $openServiceRequests === null && ! $income)
        <x-empty-state
            title="Welcome to {{ config('app.name') }}"
            description="Your account doesn't have access to any modules yet. Ask an administrator to assign you a role."
        />
    @else
        {{-- Module shortcuts + receivables/payables — the layout of the
             firm's previous system, kept because it's how the team reads
             the business at a glance, redrawn in the soft-canvas style. --}}
        @if ($modules)
            <div @class([
                'mb-5 grid grid-cols-2 gap-3 sm:gap-5',
                'lg:grid-cols-4' => count($modules) >= 4,
                'lg:grid-cols-3' => count($modules) === 3,
            ])>
                @foreach ($modules as $m)
                    <a href="{{ $m['href'] }}" wire:navigate
                       class="group relative flex items-center gap-3 overflow-hidden rounded-3xl border border-ink-200/70 bg-surface p-4 transition duration-200 hover:-translate-y-0.5 hover:border-transparent hover:bg-charcoal hover:shadow-lg hover:shadow-black/10 sm:gap-4 sm:p-5 dark:border-white/[0.06] dark:hover:bg-accent-500">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-500/15 text-accent-700 transition group-hover:bg-white/10 group-hover:text-accent-400 sm:h-12 sm:w-12 dark:text-accent-400 dark:group-hover:bg-charcoal/15 dark:group-hover:text-charcoal">
                            <x-icon :name="$m['icon']" class="h-5 w-5 sm:h-6 sm:w-6" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-base font-bold tracking-tight text-ink-900 transition group-hover:text-white sm:text-lg dark:group-hover:text-charcoal">{{ $m['label'] }}</span>
                            <span class="block truncate text-xs text-ink-500 transition group-hover:text-white/70 dark:group-hover:text-charcoal/70">
                                <span class="font-semibold tabular-nums text-ink-700 group-hover:text-white dark:group-hover:text-charcoal">{{ number_format($m['stat']) }}</span> {{ $m['unit'] }}
                            </span>
                        </span>
                        <x-icon name="arrow-up-right" class="absolute right-4 top-4 hidden h-4 w-4 text-ink-400 transition group-hover:text-accent-400 sm:block dark:group-hover:text-charcoal" />
                    </a>
                @endforeach
            </div>
        @endif

        @if ($ledgerReceivables || $ledgerPayables)
            <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                @foreach (array_filter(['receivables' => $ledgerReceivables, 'payables' => $ledgerPayables]) as $side => $card)
                    @php
                        $isIn = $side === 'receivables';
                        [$tInt, $tDec] = $money($card['total']);
                    @endphp
                    <div class="panel flex flex-col p-2">
                        {{-- Tinted header block carrying the side's total. --}}
                        <div @class([
                            'relative overflow-hidden rounded-[22px] px-5 py-5 sm:px-6',
                            'bg-gradient-to-br from-success-600 to-success-800 text-white' => $isIn,
                            'bg-charcoal text-white dark:bg-white/[0.06]' => ! $isIn,
                        ])>
                            <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full bg-white/[0.07]"></div>
                            <div class="pointer-events-none absolute -right-2 top-10 h-24 w-24 rounded-full bg-white/[0.05]"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <div>
                                    <p class="flex items-center gap-2 text-sm font-medium text-white/80">
                                        <x-icon :name="$isIn ? 'arrow-down-right' : 'arrow-up-right'" class="h-4 w-4" />
                                        {{ $isIn ? 'Receivables' : 'Payables' }}
                                    </p>
                                    <p class="mt-2 flex items-baseline gap-1.5">
                                        <span class="text-sm font-semibold text-white/70">{{ $currency }}</span>
                                        <span @class(['text-3xl font-bold tracking-tight tabular-nums sm:text-[34px]', 'text-accent-400' => ! $isIn])>{{ $tInt }}</span>
                                        <span class="text-base font-semibold text-white/60">.{{ $tDec }}</span>
                                    </p>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-medium text-white/80">{{ $isIn ? 'Owed to us' : 'We owe' }}</span>
                            </div>
                        </div>

                        {{-- One row per source: share bar (single series, so one
                             color on a neutral track) + the exact amount. --}}
                        <ul class="flex-1 divide-y divide-ink-100 px-3 py-2 sm:px-4">
                            @foreach ($card['rows'] as $r)
                                @php $pct = $card['total'] > 0 ? round($r['amount'] / $card['total'] * 100) : 0; @endphp
                                <li>
                                    <a href="{{ $r['href'] }}" wire:navigate class="group flex items-center gap-3 py-3.5">
                                        <span @class([
                                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $isIn,
                                            'bg-accent-500/15 text-accent-700 dark:text-accent-400' => ! $isIn,
                                        ])>
                                            <x-icon :name="$r['icon']" class="h-[18px] w-[18px]" />
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="flex items-baseline justify-between gap-3">
                                                <span class="truncate text-sm font-semibold text-ink-900 group-hover:underline">{{ $r['label'] }}</span>
                                                <span class="shrink-0 text-sm font-bold tabular-nums text-ink-900">{{ number_format($r['amount'], 2) }}</span>
                                            </span>
                                            <span class="mt-1.5 flex items-center gap-3">
                                                <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink-100">
                                                    <span @class([
                                                        'block h-full rounded-full',
                                                        'bg-[#059669] dark:bg-[#12A372]' => $isIn,
                                                        'bg-[#C68F00] dark:bg-[#BD8A00]' => ! $isIn,
                                                    ]) style="width: {{ $r['amount'] > 0 ? max(2, $r['amount'] / $card['total'] * 100) : 0 }}%"></span>
                                                </span>
                                                <span class="w-9 shrink-0 text-right text-[11px] font-semibold tabular-nums text-ink-500">{{ $pct }}%</span>
                                            </span>
                                            <span class="mt-1 block truncate text-[11px] text-ink-500">{{ $r['hint'] }} · {{ number_format($r['count']) }} {{ \Illuminate\Support\Str::plural('item', $r['count']) }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Quick links: the everyday actions, one tap from the dashboard.
             Each shows only when the user may do it. --}}
        @php
            $quick = array_values(array_filter([
                ['label' => 'New Lead', 'icon' => 'funnel', 'href' => route('leads.create'), 'can' => 'create_leads'],
                ['label' => 'Site Visit', 'icon' => 'map-pin', 'href' => route('site-visits.create'), 'can' => 'create_site_visits'],
                ['label' => 'Meeting', 'icon' => 'calendar', 'href' => route('meetings.create'), 'can' => 'create_meetings'],
                ['label' => 'Calls', 'icon' => 'phone', 'href' => route('calls.index'), 'can' => ['view_leads', 'view_clients']],
                ['label' => 'Quotation', 'icon' => 'file-text', 'href' => route('quotations.create'), 'can' => 'create_quotations'],
                ['label' => 'Invoice', 'icon' => 'receipt', 'href' => route('invoices.create'), 'can' => 'create_invoices'],
                ['label' => 'Design Sale', 'icon' => 'palette', 'href' => route('designs.create', ['for' => 'sold']), 'can' => 'create_designs'],
                ['label' => 'Record Payment', 'icon' => 'credit-card', 'href' => route('payments.create'), 'can' => 'create_payments'],
                ['label' => 'Expense', 'icon' => 'wallet', 'href' => route('expenses.create'), 'can' => 'create_expenses'],
                ['label' => 'Purchase Order', 'icon' => 'shopping-cart', 'href' => route('purchase-orders.create'), 'can' => 'create_purchase_orders'],
                ['label' => 'Vendor Bills', 'icon' => 'truck', 'href' => route('vendor-invoices.index'), 'can' => 'view_vendor_invoices'],
                ['label' => 'Contractors', 'icon' => 'hard-hat', 'href' => route('subcontractors.index'), 'can' => 'view_subcontractors'],
                ['label' => 'Reports', 'icon' => 'bar-chart', 'href' => route('reports.index'), 'can' => 'view_reports'],
            ], fn ($q) => (bool) auth()->user()?->canAny((array) $q['can'])));
        @endphp
        @if ($quick)
            <div class="panel mb-5 p-4 sm:p-5">
                <p class="mb-3 px-1 text-xs font-semibold text-ink-500">Quick links</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
                    @foreach ($quick as $q)
                        <a href="{{ $q['href'] }}" wire:navigate class="group flex items-center gap-2.5 rounded-full border border-ink-200/70 bg-surface py-1.5 pl-1.5 pr-3 text-sm font-medium text-ink-700 transition hover:border-transparent hover:bg-charcoal hover:text-white dark:border-white/[0.06] dark:hover:bg-accent-500 dark:hover:text-charcoal">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-500/15 text-accent-700 transition group-hover:bg-white/10 group-hover:text-accent-400 dark:text-accent-400 dark:group-hover:bg-charcoal/15 dark:group-hover:text-charcoal">
                                <x-icon :name="$q['icon']" class="h-4 w-4" />
                            </span>
                            <span class="truncate">{{ $q['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Row 1: year at a glance (circles) + revenue trend -------------- --}}
        @if ($income || $expenses)
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                @php
                    // Circle AREA follows each amount (radius ∝ √value), with
                    // a floor so a small figure still fits its label. The
                    // exact amounts are printed inside every circle.
                    $bubbles = array_values(array_filter([
                        $income ? ['key' => 'in', 'label' => 'Received', 'value' => $income['ytd']] : null,
                        $expenses ? ['key' => 'out', 'label' => 'Expenses', 'value' => $expenses['ytd']] : null,
                        $netCash !== null ? ['key' => 'net', 'label' => $netCash < 0 ? 'Net cash (deficit)' : 'Net cash', 'value' => $netCash] : null,
                    ]));
                    $bMax = max(1, ...array_map(fn ($b) => abs($b['value']), $bubbles));
                    $radius = fn ($v) => round(max(66, 116 * sqrt(abs($v) / $bMax)), 1);
                    $byKey = collect($bubbles)->keyBy('key')->map(fn ($b) => $b + ['r' => $radius($b['value'])]);
                    $in = $byKey->get('in');
                    $out = $byKey->get('out');
                    $net = $byKey->get('net');
                    // Fixed composition inside a 400×330 box: income left,
                    // expenses top-right, net cash overlapping both below.
                    if ($in) { $in['cx'] = 8 + $in['r']; $in['cy'] = 180; }
                    if ($out) { $out['cx'] = 392 - $out['r']; $out['cy'] = 8 + $out['r']; }
                    if ($net) { $net['cx'] = 262; $net['cy'] = 322 - $net['r']; }
                @endphp
                <div class="panel relative overflow-hidden p-6 lg:col-span-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="section-title">Year at a Glance</p>
                            <p class="section-sub">{{ $yearLabel }} year to date · {{ $currency }}</p>
                        </div>
                        @if ($canReports)
                            <a href="{{ route('reports.financial-summary') }}" wire:navigate class="icon-btn h-9 w-9" title="Financial summary report" aria-label="Open financial summary report">
                                <x-icon name="arrow-up-right" class="h-4 w-4" />
                            </a>
                        @endif
                    </div>

                    <svg viewBox="0 0 400 330" class="mx-auto mt-4 w-full max-w-[440px]" role="img" aria-label="Year to date: {{ collect($bubbles)->map(fn ($b) => $b['label'].' '.$currency.' '.number_format($b['value'], 2))->implode(', ') }}">
                        <defs>
                            <radialGradient id="dash-gold" cx="30%" cy="25%" r="85%">
                                <stop offset="0%" stop-color="#FCE7A6" />
                                <stop offset="55%" stop-color="#F8CF4D" />
                                <stop offset="100%" stop-color="#F4B400" />
                            </radialGradient>
                            <radialGradient id="dash-charcoal" cx="30%" cy="25%" r="90%">
                                <stop offset="0%" stop-color="#4A5046" />
                                <stop offset="100%" stop-color="#20241F" />
                            </radialGradient>
                            <filter id="dash-glass" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="10" stdDeviation="12" flood-color="#20241F" flood-opacity="0.14" />
                            </filter>
                            <radialGradient id="dash-charcoal-dark" cx="30%" cy="25%" r="90%">
                                <stop offset="0%" stop-color="#4C5147" />
                                <stop offset="100%" stop-color="#30342D" />
                            </radialGradient>
                        </defs>

                        @if ($in)
                            <g>
                                <title>Received {{ $yearLabel }} YTD: {{ $currency }} {{ number_format($in['value'], 2) }}</title>
                                <circle cx="{{ $in['cx'] }}" cy="{{ $in['cy'] }}" r="{{ $in['r'] }}" fill="url(#dash-gold)" />
                                <text x="{{ $in['cx'] - 6 }}" y="{{ $in['cy'] - 8 }}" text-anchor="middle" class="fill-[#20241F] text-[13px] font-medium opacity-80">{{ $in['label'] }}</text>
                                <text x="{{ $in['cx'] - 6 }}" y="{{ $in['cy'] + 20 }}" text-anchor="middle" class="fill-[#20241F] text-[26px] font-bold tracking-tight">{{ $compact($in['value']) }}</text>
                            </g>
                        @endif
                        @if ($out)
                            <g>
                                <title>Expenses {{ $yearLabel }} YTD: {{ $currency }} {{ number_format($out['value'], 2) }}</title>
                                <circle cx="{{ $out['cx'] }}" cy="{{ $out['cy'] }}" r="{{ $out['r'] }}" fill="url(#dash-charcoal)" class="dark:hidden" fill-opacity="0.94" />
                                <circle cx="{{ $out['cx'] }}" cy="{{ $out['cy'] }}" r="{{ $out['r'] }}" fill="url(#dash-charcoal-dark)" class="hidden dark:inline" fill-opacity="0.94" stroke="rgb(255 255 255 / 0.08)" />
                                <text x="{{ $out['cx'] + 8 }}" y="{{ $out['cy'] - 10 }}" text-anchor="middle" class="fill-white/70 text-[13px] font-medium">{{ $out['label'] }}</text>
                                <text x="{{ $out['cx'] + 8 }}" y="{{ $out['cy'] + 16 }}" text-anchor="middle" class="fill-[#F4B400] text-[23px] font-bold tracking-tight">{{ $compact($out['value']) }}</text>
                            </g>
                        @endif
                        @if ($net)
                            <g>
                                <title>{{ $net['label'] }} {{ $yearLabel }} YTD: {{ $currency }} {{ number_format($net['value'], 2) }}</title>
                                <circle cx="{{ $net['cx'] }}" cy="{{ $net['cy'] }}" r="{{ $net['r'] }}" filter="url(#dash-glass)" class="fill-white/80 stroke-[#20241F]/[0.06] dark:fill-white/10 dark:stroke-white/25" stroke-width="1.5" />
                                <text x="{{ $net['cx'] }}" y="{{ $net['cy'] - 6 }}" text-anchor="middle" class="fill-current text-[12px] font-medium text-ink-600">{{ $net['label'] }}</text>
                                <text x="{{ $net['cx'] }}" y="{{ $net['cy'] + 20 }}" text-anchor="middle" class="fill-current text-[22px] font-bold tracking-tight text-ink-900">{{ $compact($net['value']) }}</text>
                            </g>
                        @endif
                    </svg>

                    <dl class="mt-3 grid grid-cols-3 gap-2 border-t border-ink-100 pt-4 text-center">
                        @foreach ($bubbles as $b)
                            <div>
                                <dt class="truncate text-[11px] font-medium text-ink-500">{{ $b['label'] }}</dt>
                                <dd class="mt-0.5 truncate text-xs font-bold tabular-nums text-ink-900 sm:text-sm">{{ number_format($b['value'], 2) }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($income)
                        {{-- Design work is also sold without a project; show
                             how much of this year's income came from it. --}}
                        <p class="mt-3 flex items-center justify-between gap-2 rounded-2xl bg-ink-50 px-4 py-2.5 text-xs text-ink-600 dark:bg-white/[0.04]">
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <x-icon name="palette" class="h-3.5 w-3.5 text-ink-500" /> Design sales {{ $yearLabel }}
                            </span>
                            <span class="font-semibold tabular-nums text-ink-900">
                                {{ number_format($income['designYtd'], 2) }}
                                @if ($income['ytd'] > 0)
                                    <span class="font-medium text-ink-500">· {{ round($income['designYtd'] / $income['ytd'] * 100) }}% of received</span>
                                @endif
                            </span>
                        </p>
                    @endif
                </div>

                @if ($income)
                    @php [$curInt, $curDec] = $money($income['current']); @endphp
                    <div class="panel flex flex-col p-6 lg:col-span-7"
                        x-data="{
                            init() {
                                const t = window.psChartTheme();
                                // Dashed guide line under the hovered month (the reference's crosshair).
                                const guide = {
                                    id: 'guide',
                                    afterDatasetsDraw(chart) {
                                        const a = chart.tooltip?.getActiveElements?.() ?? [];
                                        if (! a.length) return;
                                        const x = a[0].element.x, { top, bottom } = chart.chartArea, ctx = chart.ctx;
                                        ctx.save(); ctx.setLineDash([4, 4]); ctx.lineWidth = 1; ctx.strokeStyle = t.baseline;
                                        ctx.beginPath(); ctx.moveTo(x, top); ctx.lineTo(x, bottom); ctx.stroke(); ctx.restore();
                                    },
                                };
                                const datasets = [{
                                    label: 'Received', data: @js($income['monthly']), borderColor: t.income, backgroundColor: t.income,
                                    borderWidth: 2, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
                                    pointBackgroundColor: t.income, pointBorderColor: t.surface, pointBorderWidth: 2, order: 1,
                                }];
                                @if ($expenses)
                                    datasets.push({
                                        label: 'Expenses', data: @js($expenses['monthly']), borderColor: t.expense, backgroundColor: t.expenseFill,
                                        fill: 'origin', borderWidth: 2, tension: 0.4, pointRadius: 0, pointHoverRadius: 5,
                                        pointBackgroundColor: t.expense, pointBorderColor: t.surface, pointBorderWidth: 2, order: 2,
                                    });
                                @endif
                                new window.Chart(this.$refs.trend, {
                                    type: 'line',
                                    data: { labels: @js($chartMonths), datasets },
                                    plugins: [guide],
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        interaction: { mode: 'index', intersect: false },
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: {
                                                backgroundColor: t.tooltipBg, titleColor: t.tooltipText, bodyColor: t.tooltipText,
                                                padding: 10, cornerRadius: 12, boxPadding: 4, usePointStyle: true,
                                                callbacks: { label: (c) => ' ' + c.dataset.label + ': {{ $currency }} ' + Number(c.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
                                            },
                                        },
                                        scales: {
                                            x: { grid: { display: false }, border: { display: false }, ticks: { color: t.text, padding: 6 } },
                                            y: { beginAtZero: true, grid: { color: t.grid }, border: { display: false }, ticks: { color: t.text, maxTicksLimit: 5, padding: 8 } },
                                        },
                                    },
                                });
                            },
                        }"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="section-title">Revenue Trend</p>
                                <p class="mt-3 text-xs text-ink-500">Received this month</p>
                                <p class="mt-0.5 flex flex-wrap items-baseline gap-x-1 text-ink-900">
                                    <span class="text-sm font-semibold text-ink-400">{{ $currency }}</span>
                                    <span class="text-[28px] font-bold leading-none tracking-tight">{{ $curInt }}</span><span class="text-sm font-semibold text-ink-500">.{{ $curDec }}</span>
                                    @if ($income['delta'] !== null)
                                        <span @class([
                                            'ml-2 inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold',
                                            'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' => $income['delta'] >= 0,
                                            'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-300' => $income['delta'] < 0,
                                        ])>
                                            <x-icon :name="$income['delta'] >= 0 ? 'arrow-up-right' : 'arrow-down-right'" class="h-3 w-3" />{{ number_format(abs($income['delta']), 1) }}% vs last month
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-3">
                                <span class="chip">Last 6 months</span>
                                <div class="flex items-center gap-4 text-xs font-medium text-ink-600">
                                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#059669] dark:bg-[#12A372]"></span>Received</span>
                                    @if ($expenses)
                                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-[#C68F00] dark:bg-[#BD8A00]"></span>Expenses</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="relative mt-4 min-h-60 flex-1"><canvas x-ref="trend" role="img" aria-label="Money received{{ $expenses ? ' and expenses' : '' }} per month, last 6 months"></canvas></div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Row 2: health rings + invoice status bars ---------------------- --}}
        @php
            $rings = array_values(array_filter([
                $ratios ? ['label' => 'Collection', 'value' => $ratios['collection'], 'tone' => 'text-[#059669] dark:text-[#12A372]', 'dot' => 'bg-[#059669] dark:bg-[#12A372]', 'note' => 'Collected ÷ billed · goal 90%+'] : null,
                $margin !== null || $netCash !== null ? ['label' => 'Profit margin', 'value' => $margin, 'tone' => 'text-[#C68F00] dark:text-[#BD8A00]', 'dot' => 'bg-[#C68F00] dark:bg-[#BD8A00]', 'note' => 'Left after expenses · '.$yearLabel] : null,
                $avgProjectProgress !== null ? ['label' => 'Project progress', 'value' => $avgProjectProgress, 'tone' => 'text-[#2a78d6] dark:text-[#3987e5]', 'dot' => 'bg-[#2a78d6] dark:bg-[#3987e5]', 'note' => 'Average across '.($activeProjects ?? 0).' active'] : null,
            ]));
            $invoiceRows = $invoicesByStatus !== null
                ? collect(\App\Enums\InvoiceStatus::cases())->map(fn ($c) => ['label' => $c->label(), 'count' => $invoicesByStatus[$c->value] ?? 0])->all()
                : null;
        @endphp
        @if (count($rings) || $invoiceRows !== null)
            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-12">
                @if (count($rings))
                    <div @class(['panel p-6', 'lg:col-span-5' => $invoiceRows !== null, 'lg:col-span-12' => $invoiceRows === null])>
                        <p class="section-title">Financial Health</p>
                        <p class="section-sub">Each ring is one share out of 100%</p>

                        <div class="mt-5 flex flex-col items-center gap-6 sm:flex-row sm:items-center">
                            {{-- Concentric single-proportion rings, each on its own neutral track. --}}
                            <div class="relative h-52 w-52 shrink-0">
                                <svg viewBox="0 0 200 200" class="h-full w-full -rotate-90" aria-hidden="true">
                                    @foreach ($rings as $i => $ring)
                                        @php
                                            $rr = 88 - $i * 19;
                                            $circ = 2 * M_PI * $rr;
                                            $pct = $ring['value'] === null ? 0 : max(0, min(100, (float) $ring['value']));
                                        @endphp
                                        <circle cx="100" cy="100" r="{{ $rr }}" fill="none" stroke-width="13" class="stroke-current text-ink-100" />
                                        @if ($pct > 0)
                                            <circle cx="100" cy="100" r="{{ $rr }}" fill="none" stroke-width="13" stroke-linecap="round"
                                                stroke-dasharray="{{ round($circ, 2) }}" stroke-dashoffset="{{ round($circ * (1 - $pct / 100), 2) }}"
                                                class="stroke-current {{ $ring['tone'] }} transition-[stroke-dashoffset] duration-700">
                                                <title>{{ $ring['label'] }}: {{ $pctText($ring['value']) }}</title>
                                            </circle>
                                        @endif
                                    @endforeach
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                    <span class="text-[11px] font-medium text-ink-500">{{ $rings[0]['label'] }}</span>
                                    <span class="text-2xl font-bold tracking-tight text-ink-900">{{ $pctText($rings[0]['value']) }}</span>
                                </div>
                            </div>

                            <ul class="w-full space-y-3.5">
                                @foreach ($rings as $ring)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $ring['dot'] }}"></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-baseline justify-between gap-3">
                                                <span class="text-sm font-medium text-ink-700">{{ $ring['label'] }}</span>
                                                <span class="text-sm font-bold tabular-nums text-ink-900">{{ $pctText($ring['value']) }}</span>
                                            </div>
                                            <p class="text-xs text-ink-500">{{ $ring['note'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if ($invoiceRows !== null)
                    <div @class(['panel p-6', 'lg:col-span-7' => count($rings), 'lg:col-span-12' => ! count($rings)])>
                        <div class="mb-5 flex items-start justify-between gap-3">
                            <div>
                                <p class="section-title">Invoice Status</p>
                                <p class="section-sub">Share of all invoices by status</p>
                            </div>
                            <a href="{{ route('invoices.index') }}" wire:navigate class="icon-btn h-9 w-9" title="All invoices" aria-label="Open invoices">
                                <x-icon name="arrow-up-right" class="h-4 w-4" />
                            </a>
                        </div>
                        <x-status-bars :rows="$invoiceRows" unit="invoices" empty="No invoices yet." />
                    </div>
                @endif
            </div>
        @endif

        {{-- Row 3: month-over-month KPI cards ------------------------------ --}}
        @if ($hasFinance)
            @php
                $toneIncome = 'text-[#059669] dark:text-[#12A372]';
                $toneGold = 'text-[#C68F00] dark:text-[#BD8A00]';
                $kpis = array_values(array_filter([
                    $income ? ['label' => 'Total Income', 'icon' => 'landmark', 'sub' => 'Payments received · this month', 'amount' => $income['current'], 'delta' => $income['delta'], 'upIsGood' => true, 'spark' => $income['spark'], 'tone' => $toneIncome, 'href' => route('payments.index')] : null,
                    $expenses ? ['label' => 'Total Expenses', 'icon' => 'receipt', 'sub' => 'Recorded · this month', 'amount' => $expenses['current'], 'delta' => $expenses['delta'], 'upIsGood' => false, 'spark' => $expenses['spark'], 'tone' => $toneGold, 'href' => route('expenses.index')] : null,
                    $invoiced ? ['label' => 'Invoiced', 'icon' => 'file-text', 'sub' => 'Billed to clients · this month', 'amount' => $invoiced['current'], 'delta' => $invoiced['delta'], 'upIsGood' => true, 'spark' => $invoiced['spark'], 'tone' => $toneIncome, 'href' => route('invoices.index')] : null,
                    $receivables ? ['label' => 'Accounts Receivable', 'icon' => 'gauge', 'sub' => $receivables['count'].' open invoice'.($receivables['count'] === 1 ? '' : 's'), 'amount' => $receivables['total'], 'delta' => null, 'upIsGood' => false, 'spark' => null, 'tone' => $toneGold, 'href' => route('invoices.index', ['status' => 'issued']), 'receivable' => $receivables] : null,
                ]));
            @endphp
            @if (count($kpis))
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($kpis as $kpi)
                        @php [$kInt, $kDec] = $money($kpi['amount']); @endphp
                        <a href="{{ $kpi['href'] }}" wire:navigate class="panel group flex flex-col p-5 transition hover:-translate-y-0.5 hover:shadow-soft">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ink-50 text-ink-600 dark:bg-ink-100">
                                    <x-icon :name="$kpi['icon']" class="h-[18px] w-[18px]" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-900">{{ $kpi['label'] }}</p>
                                    <p class="truncate text-xs text-ink-500">{{ $kpi['sub'] }}</p>
                                </div>
                                <x-icon name="arrow-up-right" class="h-4 w-4 shrink-0 text-ink-300 transition group-hover:text-ink-700" />
                            </div>

                            <p class="mt-5 flex items-baseline gap-1 text-ink-900">
                                <span class="text-sm font-semibold text-ink-400">{{ $currency }}</span>
                                <span class="text-[26px] font-bold leading-none tracking-tight">{{ $kInt }}</span><span class="text-sm font-semibold text-ink-500">.{{ $kDec }}</span>
                            </p>

                            <div class="mt-4 h-9">
                                @if ($kpi['spark'])
                                    <x-sparkbars :points="$kpi['spark']" :tone="$kpi['tone']" :currency="$currency" />
                                @elseif (isset($kpi['receivable']))
                                    @php
                                        $r = $kpi['receivable'];
                                        $overduePct = $r['total'] > 0 ? round($r['overdueAmount'] / $r['total'] * 100) : 0;
                                    @endphp
                                    {{-- Current vs overdue split of the open balance. --}}
                                    <div class="flex h-3 overflow-hidden rounded-full bg-ink-100" title="Overdue {{ $overduePct }}% · Current {{ 100 - $overduePct }}%">
                                        <div class="h-3 rounded-full bg-[#059669] dark:bg-[#12A372]" style="width: {{ 100 - $overduePct }}%"></div>
                                        @if ($overduePct > 0)
                                            <div class="ml-0.5 h-3 rounded-full bg-[#C68F00] dark:bg-[#BD8A00]" style="width: {{ $overduePct }}%"></div>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 flex justify-between text-[11px] text-ink-500">
                                        <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-[#059669] dark:bg-[#12A372]"></span>Current</span>
                                        <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-[#C68F00] dark:bg-[#BD8A00]"></span>Overdue · {{ $r['overdueCount'] }}</span>
                                    </div>
                                @endif
                            </div>

                            <p class="mt-3 flex items-center gap-1.5 text-xs text-ink-500">
                                @if (isset($kpi['receivable']))
                                    <span class="font-semibold text-ink-700">{{ number_format($kpi['receivable']['overdueAmount'], 2) }}</span> past due
                                @elseif ($kpi['delta'] === null)
                                    No figures for last month to compare
                                @else
                                    @php $isGood = ($kpi['delta'] >= 0) === $kpi['upIsGood']; @endphp
                                    <span @class([
                                        'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 font-semibold',
                                        'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500' => $isGood,
                                        'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-300' => ! $isGood,
                                    ])>
                                        <x-icon :name="$kpi['delta'] >= 0 ? 'arrow-up-right' : 'arrow-down-right'" class="h-3 w-3" />{{ number_format(abs($kpi['delta']), 1) }}%
                                    </span>
                                    {{ $kpi['delta'] >= 0 ? 'higher' : 'lower' }} vs previous month
                                @endif
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Operations snapshot data ------------------------------------------ --}}
        @php
            $snapshot = array_filter([
                'Sales & CRM' => array_filter([
                    $totalLeads !== null ? ['Total leads', $totalLeads, route('leads.index')] : null,
                    $newLeads !== null ? ['New leads', $newLeads, route('leads.index', ['status' => 'new'])] : null,
                    $openLeads !== null ? ['Open leads', $openLeads, route('leads.index')] : null,
                    $upcomingSiteVisits !== null ? ['Upcoming site visits', $upcomingSiteVisits, route('site-visits.index')] : null,
                    $quotationsAwaitingDecision !== null ? ['Quotations awaiting decision', $quotationsAwaitingDecision, route('quotations.index')] : null,
                    $totalClients !== null ? ['Clients', $totalClients, route('clients.index')] : null,
                ]),
                'Projects & Design' => array_filter([
                    $activeProjects !== null ? ['Active projects', $activeProjects, route('projects.index')] : null,
                    $projectsOnHold !== null ? ['On hold', $projectsOnHold, route('projects.index', ['status' => 'on_hold'])] : null,
                    $designsAwaitingDecision !== null ? ['Designs awaiting decision', $designsAwaitingDecision, route('designs.index', ['status' => 'under_review'])] : null,
                    $changeOrdersPending !== null ? ['Change orders pending', $changeOrdersPending, route('change-orders.index', ['status' => 'pending'])] : null,
                ]),
                'Supply Chain' => array_filter([
                    $lowStockProducts !== null ? ['Low-stock products', $lowStockProducts, route('products.index', ['lowStock' => true])] : null,
                    $pendingRequisitions !== null ? ['Pending requisitions', $pendingRequisitions, route('material-requisitions.index')] : null,
                    $pendingPurchaseOrders !== null ? ['Open purchase orders', $pendingPurchaseOrders, route('purchase-orders.index')] : null,
                ]),
                'People & Quality' => array_filter([
                    $pendingLeaveRequests !== null ? ['Pending leave requests', $pendingLeaveRequests, route('leaves.index')] : null,
                    $payrollDue !== null ? ['Payroll due this month', $payrollDue, route('payroll.index')] : null,
                    $openSnags !== null ? ['Open snags', $openSnags, route('snags.index')] : null,
                    $openServiceRequests !== null ? ['Open service requests', $openServiceRequests, route('warranties.index')] : null,
                ]),
            ]);
        @endphp

        {{-- Row 4: largest open invoices + operations snapshot -------------- --}}
        @if ($topOpenInvoices || count($snapshot))
            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-12">
                @if ($topOpenInvoices)
                    <div @class(['panel flex flex-col p-2', 'lg:col-span-5' => count($snapshot), 'lg:col-span-12' => ! count($snapshot)])>
                        <div class="flex items-start justify-between gap-3 px-4 pb-3 pt-4">
                            <div>
                                <p class="section-title">Largest Open Invoices</p>
                                <p class="section-sub">Highest unpaid balances ({{ $currency }})</p>
                            </div>
                            <a href="{{ route('invoices.index') }}" wire:navigate class="chip hover:border-ink-300 hover:text-ink-900">View all</a>
                        </div>
                        <ul class="flex-1 space-y-1">
                            @forelse ($topOpenInvoices as $inv)
                                @php $isOverdue = $inv->due_date && $inv->due_date->lt(today()); @endphp
                                <li>
                                    <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="flex items-center gap-3 rounded-2xl px-4 py-2.5 transition hover:bg-ink-50">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-50 text-xs font-bold text-accent-700 dark:text-accent-600">
                                            {{ strtoupper(substr($inv->client?->name ?? '?', 0, 1)) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-ink-900">{{ $inv->client?->name ?? '—' }}</p>
                                            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-ink-500">
                                                {{ $inv->invoice_no }} ·
                                                @if ($isOverdue)
                                                    <x-icon name="alert-triangle" class="h-3 w-3 text-danger-600" /><span class="font-semibold text-danger-600 dark:text-danger-300">Overdue</span>
                                                @else
                                                    Due {{ $inv->due_date?->format('d M') ?? '—' }}
                                                @endif
                                            </p>
                                        </div>
                                        <p class="text-sm font-bold tabular-nums text-ink-900">{{ number_format($inv->outstandingAmount(), 2) }}</p>
                                    </a>
                                </li>
                            @empty
                                <li class="px-4 py-10 text-center text-sm text-ink-500">No open invoices — everything billed is collected.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif

                @if (count($snapshot))
                    <div @class(['panel p-6', 'lg:col-span-7' => $topOpenInvoices, 'lg:col-span-12' => ! $topOpenInvoices])>
                        <p class="section-title">Operations Snapshot</p>
                        <p class="section-sub">Live counts across every module you can access — click any row to open the list.</p>
                        <div class="mt-5 grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                            @foreach ($snapshot as $section => $rows)
                                <div>
                                    <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.1em] text-ink-400">{{ $section }}</p>
                                    <ul class="space-y-1">
                                        @foreach ($rows as $row)
                                            @php [$label, $value, $href] = $row; @endphp
                                            <li>
                                                <a href="{{ $href }}" wire:navigate class="group -mx-2 flex items-center justify-between gap-3 rounded-full px-3 py-1.5 text-sm transition hover:bg-ink-50">
                                                    <span class="truncate text-ink-600 group-hover:text-ink-900">{{ $label }}</span>
                                                    <span @class([
                                                        'min-w-[2.25rem] rounded-full px-2.5 py-0.5 text-center text-xs font-bold tabular-nums',
                                                        'bg-charcoal text-accent-500 dark:bg-accent-500 dark:text-charcoal' => $value > 0,
                                                        'bg-ink-100 text-ink-500' => $value == 0,
                                                    ])>{{ number_format($value) }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Row 5: lead + project status bars ------------------------------ --}}
        @php
            $statusCards = array_values(array_filter([
                $leadsByStatus !== null ? ['title' => 'Lead Pipeline', 'sub' => 'Share of all leads by stage', 'unit' => 'leads', 'href' => route('leads.index'), 'rows' => collect(\App\Enums\LeadStatus::cases())->map(fn ($c) => ['label' => $c->label(), 'count' => $leadsByStatus[$c->value] ?? 0])->all()] : null,
                $projectsByStatus !== null ? ['title' => 'Project Status', 'sub' => 'Share of all projects by status', 'unit' => 'projects', 'href' => route('projects.index'), 'rows' => collect(\App\Enums\ProjectStatus::cases())->map(fn ($c) => ['label' => $c->label(), 'count' => $projectsByStatus[$c->value] ?? 0])->all()] : null,
            ]));
        @endphp
        @if (count($statusCards))
            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                @foreach ($statusCards as $card)
                    <div class="panel p-6">
                        <div class="mb-5 flex items-start justify-between gap-3">
                            <div>
                                <p class="section-title">{{ $card['title'] }}</p>
                                <p class="section-sub">{{ $card['sub'] }}</p>
                            </div>
                            <a href="{{ $card['href'] }}" wire:navigate class="icon-btn h-9 w-9" title="Open {{ $card['unit'] }}" aria-label="Open {{ $card['unit'] }}">
                                <x-icon name="arrow-up-right" class="h-4 w-4" />
                            </a>
                        </div>
                        <x-status-bars :rows="$card['rows']" :unit="$card['unit']" :empty="'No '.$card['unit'].' yet.'" />
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
