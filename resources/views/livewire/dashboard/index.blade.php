<div>
    {{--
        Brand-layout pass: a finance-first overview modeled on the
        reference "finance monitoring" dashboard — headline rings, a net-
        cash card, ratio bars with goals, four month-over-month KPI cards
        with daily sparklines, a 6-month cash-flow chart, the largest open
        invoices, then a compact operations snapshot and status breakdowns.
        Every number comes from App\Livewire\Dashboard\Index (real rows;
        null = the user can't see that module, or there's no data — the
        view says so rather than inventing a value). Series colors were
        validated as a set for light and dark surfaces (see
        window.psChartTheme in resources/js/app.js); the same hexes are
        used below as literal Tailwind arbitrary values so the scanner
        sees them.
    --}}
    @php
        $firstName = \Illuminate\Support\Str::of(\Illuminate\Support\Facades\Auth::user()->name)->before(' ');
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        $toneIncome = 'text-[#059669] dark:text-[#12A372]';
        $toneGold = 'text-[#C68F00] dark:text-[#BD8A00]';

        // Splits 1234.5 into ['1,234', '50'] so the decimals can be set
        // smaller, as in the reference layout.
        $money = function ($v) {
            [$int, $dec] = explode('.', number_format((float) $v, 2));

            return [$int, $dec];
        };

        $hasFinance = $income || $expenses || $invoiced || $receivables;
        $canReports = \Illuminate\Support\Facades\Auth::user()->can('view_reports');
    @endphp

    {{-- Greeting -------------------------------------------------------- --}}
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink-900 sm:text-[28px]">{{ $greeting }}, {{ $firstName }}!</h1>
            <p class="mt-1 text-sm text-ink-500">Your business performance and monitoring overview — {{ now()->format('l, j F Y') }}.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-lg border border-ink-200 bg-surface px-3 py-2 text-xs font-semibold text-ink-700">
                <span class="h-2 w-2 rounded-full bg-accent-500"></span> {{ $monthLabel }}
            </span>
            @if ($canReports)
                <a href="{{ route('reports.index') }}" wire:navigate class="btn-primary py-2 text-xs">View reports</a>
            @endif
        </div>
    </div>

    @if ($totalLeads === null && $totalClients === null && $upcomingSiteVisits === null && $activeProjects === null && $designsAwaitingDecision === null && $quotationsAwaitingDecision === null && $changeOrdersPending === null && $outstandingInvoicesCount === null && $expensesThisMonth === null && $lowStockProducts === null && $pendingRequisitions === null && $pendingPurchaseOrders === null && $pendingLeaveRequests === null && $payrollDue === null && $openSnags === null && $openServiceRequests === null && ! $income)
        <x-empty-state
            title="Welcome to {{ config('app.name') }}"
            description="Your account doesn't have access to any modules yet. Ask an administrator to assign you a role."
        />
    @else
        {{-- Row 1: headline rings + net cash -------------------------------- --}}
        @if ($margin !== null || $netCash !== null || $avgProjectProgress !== null)
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-12">
                @if ($netCash !== null)
                    <div class="panel p-6 lg:col-span-3">
                        <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">Profit Margin</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-500">Share of money received this year left after expenses.</p>
                        <x-ring :value="$margin" :tone="$toneGold" label="Profit margin {{ $yearLabel }}" class="mt-6" />
                        <p class="mt-4 text-center text-xs text-ink-500">{{ $yearLabel }} year to date</p>
                    </div>
                @endif

                @if ($avgProjectProgress !== null)
                    <div class="panel p-6 lg:col-span-3">
                        <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">Project Progress</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-500">Average completion across all active projects.</p>
                        <x-ring :value="$avgProjectProgress" :tone="$toneIncome" label="Average project progress" class="mt-6" />
                        <p class="mt-4 text-center text-xs text-ink-500">
                            <a href="{{ route('projects.index') }}" wire:navigate class="font-semibold text-accent-600 hover:underline">{{ $activeProjects ?? 0 }} active projects</a>
                        </p>
                    </div>
                @endif

                @if ($netCash !== null)
                    @php
                        [$ncInt, $ncDec] = $money(abs($netCash));
                        $net = array_map(fn ($a, $b) => $a - $b, $income['monthly'], $expenses['monthly']);
                        $nMin = min($net);
                        $nMax = max($net);
                        $span = ($nMax - $nMin) ?: 1;
                        $pts = collect($net)->map(fn ($v, $i) => [
                            'x' => round(10 + $i * (380 / 5), 1),
                            'y' => round(110 - (($v - $nMin) / $span) * 80, 1),
                            'v' => $v,
                        ]);
                        $line = $pts->map(fn ($p) => $p['x'].','.$p['y'])->implode(' ');
                    @endphp
                    <div class="panel p-6 md:col-span-2 lg:col-span-6">

                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-ink-500">Net Cash Position · {{ $yearLabel }} YTD</p>
                                    <p class="mt-2 flex items-baseline gap-1 text-ink-900">
                                        <span class="text-xl font-semibold text-ink-400">{{ $netCash < 0 ? '−' : '' }}{{ $currency }}</span>
                                        <span class="text-4xl font-extrabold tracking-tight">{{ $ncInt }}</span><span class="text-xl font-semibold text-ink-500">.{{ $ncDec }}</span>
                                    </p>
                                </div>
                                <div class="flex h-10 items-center rounded-lg bg-accent-500 px-3 text-sm font-extrabold tracking-wide text-charcoal">
                                    {{ strtoupper(substr(\App\Models\Setting::get('company.name', config('app.name')), 0, 2)) }}
                                </div>
                            </div>

                            {{-- Monthly net cash (received − expenses), last 6 months —
                                 in normal flow between the headline and the
                                 stats so it never runs through any figure. --}}
                            <div class="mt-3">
                                <svg viewBox="0 0 400 130" preserveAspectRatio="none" class="h-24 w-full" role="img" aria-label="Monthly net cash, last 6 months">
                                    <polygon points="10,130 {{ $line }} 390,130" class="fill-current text-accent-100/60" />
                                    <polyline points="{{ $line }}" fill="none" stroke-width="2" stroke-linejoin="round" vector-effect="non-scaling-stroke" class="stroke-current text-accent-500" />
                                    @foreach ($pts as $i => $p)
                                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="9" fill="transparent"><title>{{ $chartMonths[$i] }}: {{ $currency }} {{ number_format($p['v'], 2) }} net</title></circle>
                                    @endforeach
                                </svg>
                                <div class="mt-1 flex justify-between px-1 text-[10px] font-semibold uppercase tracking-wider text-ink-400">
                                    @foreach ($chartMonths as $m)
                                        <span>{{ $m }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-2 border-t border-ink-100 pt-4 sm:grid-cols-3 sm:gap-4">
                                <div class="flex items-baseline justify-between gap-3 sm:block">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Received</p>
                                    <p class="text-base font-bold tabular-nums text-ink-900 sm:mt-1">{{ number_format($income['ytd'], 2) }}</p>
                                </div>
                                <div class="flex items-baseline justify-between gap-3 sm:block">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Expenses</p>
                                    <p class="text-base font-bold tabular-nums text-ink-900 sm:mt-1">{{ number_format($expenses['ytd'], 2) }}</p>
                                </div>
                                <div class="flex items-baseline justify-between gap-3 sm:block">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Receivable</p>
                                    <p class="text-base font-bold tabular-nums text-ink-900 sm:mt-1">{{ $receivables ? number_format($receivables['total'], 2) : '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Row 2: ratio bars + month-over-month KPI cards ------------------- --}}
        @if ($hasFinance)
            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-12">
                @if ($ratios)
                    @php
                        $ratioRows = [
                            [
                                'icon' => 'trending-up',
                                'label' => 'Collection Ratio',
                                'value' => $ratios['collection'],
                                'goal' => 90,
                                'goalText' => 'Goal: 90% or higher',
                                'good' => $ratios['collection'] !== null && $ratios['collection'] >= 90,
                                'desc' => 'Amount collected ÷ total billed on all non-cancelled invoices.',
                            ],
                            [
                                'icon' => 'activity',
                                'label' => 'Overdue Share',
                                'value' => $ratios['overdueShare'],
                                'goal' => 20,
                                'goalText' => 'Goal: 20% or lower',
                                'good' => $ratios['overdueShare'] !== null && $ratios['overdueShare'] <= 20,
                                'desc' => 'Past-due balance ÷ total open receivables.',
                            ],
                        ];
                    @endphp
                    <div class="panel divide-y divide-ink-100 lg:col-span-5">
                        @foreach ($ratioRows as $row)
                            <div class="flex gap-5 p-6">
                                <div class="hidden h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-ink-50 text-ink-300 sm:flex">
                                    <x-icon :name="$row['icon']" class="h-10 w-10" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">{{ $row['label'] }}</p>
                                    <p class="mt-1 text-[28px] font-extrabold leading-none tracking-tight text-ink-900">
                                        {{ $row['value'] === null ? '—' : rtrim(rtrim(number_format($row['value'], 1), '0'), '.').'%' }}
                                    </p>
                                    <div class="relative mt-3 h-2 rounded-full bg-ink-100" title="{{ $row['label'] }}: {{ $row['value'] === null ? 'no data' : $row['value'].'%' }} · {{ $row['goalText'] }}">
                                        @if ($row['value'] !== null)
                                            <div @class([
                                                'h-2 rounded-full',
                                                'bg-success-500' => $row['good'],
                                                'bg-accent-500' => ! $row['good'],
                                            ]) style="width: {{ max(2, min(100, $row['value'])) }}%"></div>
                                        @endif
                                        {{-- Goal marker --}}
                                        <span class="absolute -top-1 h-4 w-0.5 rounded bg-ink-500" style="left: {{ $row['goal'] }}%"></span>
                                    </div>
                                    <p class="mt-2 flex items-center gap-1.5 text-xs text-ink-500">
                                        @if ($row['value'] !== null)
                                            <x-icon :name="$row['good'] ? 'check' : 'alert-triangle'" @class(['h-3.5 w-3.5', 'text-success-600' => $row['good'], 'text-warning-600' => ! $row['good']]) />
                                            <span class="font-semibold text-ink-700">{{ $row['good'] ? 'On target' : 'Below target' }}</span> ·
                                        @endif
                                        {{ $row['goalText'] }}
                                    </p>
                                    <p class="mt-2 text-sm leading-relaxed text-ink-600">
                                        {{ $row['desc'] }}
                                        @if ($canReports)
                                            <a href="{{ route('reports.financial-summary') }}" wire:navigate class="font-semibold text-accent-600 hover:underline">View report</a>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @php
                    $kpis = array_values(array_filter([
                        $income ? ['label' => 'Total Income', 'sub' => 'Payments received · this month', 'amount' => $income['current'], 'delta' => $income['delta'], 'upIsGood' => true, 'spark' => $income['spark'], 'tone' => $toneIncome, 'href' => route('payments.index')] : null,
                        $expenses ? ['label' => 'Total Expenses', 'sub' => 'Recorded · this month', 'amount' => $expenses['current'], 'delta' => $expenses['delta'], 'upIsGood' => false, 'spark' => $expenses['spark'], 'tone' => $toneGold, 'href' => route('expenses.index')] : null,
                        $invoiced ? ['label' => 'Invoiced', 'sub' => 'Billed to clients · this month', 'amount' => $invoiced['current'], 'delta' => $invoiced['delta'], 'upIsGood' => true, 'spark' => $invoiced['spark'], 'tone' => $toneIncome, 'href' => route('invoices.index')] : null,
                        $receivables ? ['label' => 'Accounts Receivable', 'sub' => $receivables['count'].' open invoice'.($receivables['count'] === 1 ? '' : 's'), 'amount' => $receivables['total'], 'delta' => null, 'upIsGood' => false, 'spark' => null, 'tone' => $toneGold, 'href' => route('invoices.index', ['status' => 'issued']), 'receivable' => $receivables] : null,
                    ]));
                @endphp
                @if (count($kpis))
                    <div @class(['grid grid-cols-1 gap-5 sm:grid-cols-2', 'lg:col-span-7' => $ratios, 'lg:col-span-12 lg:grid-cols-4' => ! $ratios])>
                        @foreach ($kpis as $kpi)
                            @php [$kInt, $kDec] = $money($kpi['amount']); @endphp
                            <a href="{{ $kpi['href'] }}" wire:navigate class="panel group flex flex-col p-5 transition hover:-translate-y-0.5 hover:border-ink-300 hover:shadow-soft">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">{{ $kpi['label'] }}</p>
                                        <p class="mt-0.5 text-xs text-ink-500">{{ $kpi['sub'] }}</p>
                                    </div>
                                    <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-ink-300 transition group-hover:translate-x-0.5 group-hover:text-ink-500" />
                                </div>

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
                                            <div class="h-3 bg-[#059669] dark:bg-[#12A372]" style="width: {{ 100 - $overduePct }}%"></div>
                                            @if ($overduePct > 0)
                                                <div class="ml-0.5 h-3 bg-[#C68F00] dark:bg-[#BD8A00]" style="width: {{ $overduePct }}%"></div>
                                            @endif
                                        </div>
                                        <div class="mt-1.5 flex justify-between text-[11px] text-ink-500">
                                            <span><span class="mr-1 inline-block h-2 w-2 rounded-sm bg-[#059669] dark:bg-[#12A372]"></span>Current</span>
                                            <span><span class="mr-1 inline-block h-2 w-2 rounded-sm bg-[#C68F00] dark:bg-[#BD8A00]"></span>Overdue · {{ $r['overdueCount'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="mt-3 flex items-baseline gap-1 text-ink-900">
                                    <span class="text-sm font-semibold text-ink-400">{{ $currency }}</span>
                                    <span class="text-[26px] font-extrabold leading-none tracking-tight">{{ $kInt }}</span><span class="text-sm font-semibold text-ink-500">.{{ $kDec }}</span>
                                </p>

                                <p class="mt-2 flex items-center gap-1 text-xs text-ink-500">
                                    @if (isset($kpi['receivable']))
                                        <span class="font-semibold text-ink-700">{{ number_format($kpi['receivable']['overdueAmount'], 2) }}</span> past due
                                    @elseif ($kpi['delta'] === null)
                                        No figures for last month to compare
                                    @else
                                        @php $isGood = ($kpi['delta'] >= 0) === $kpi['upIsGood']; @endphp
                                        <x-icon :name="$kpi['delta'] >= 0 ? 'arrow-up-right' : 'arrow-down-right'" @class(['h-3.5 w-3.5', 'text-success-700 dark:text-success-500' => $isGood, 'text-danger-600 dark:text-danger-300' => ! $isGood]) />
                                        <span @class(['font-bold', 'text-success-700 dark:text-success-500' => $isGood, 'text-danger-600 dark:text-danger-300' => ! $isGood])>{{ number_format(abs($kpi['delta']), 1) }}%</span>
                                        {{ $kpi['delta'] >= 0 ? 'higher' : 'lower' }} vs previous month
                                    @endif
                                </p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- Row 3: 6-month cash flow + largest open invoices ----------------- --}}
        @if ($income || $topOpenInvoices)
            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-12">
                @if ($income)
                    <div class="panel p-6 {{ $topOpenInvoices ? 'lg:col-span-8' : 'lg:col-span-12' }}"
                        x-data="{
                            init() {
                                const t = window.psChartTheme();
                                const datasets = [{ label: 'Income', data: @js($income['monthly']), backgroundColor: t.income, borderRadius: 4, borderSkipped: 'start', maxBarThickness: 22 }];
                                @if ($expenses)
                                    datasets.push({ label: 'Expenses', data: @js($expenses['monthly']), backgroundColor: t.expense, borderRadius: 4, borderSkipped: 'start', maxBarThickness: 22 });
                                @endif
                                new window.Chart(this.$refs.cashflow, {
                                    type: 'bar',
                                    data: { labels: @js($chartMonths), datasets },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        interaction: { mode: 'index', intersect: false },
                                        datasets: { bar: { categoryPercentage: 0.6, barPercentage: 0.9 } },
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: { callbacks: { label: (c) => c.dataset.label + ': {{ $currency }} ' + Number(c.parsed.y).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) } },
                                        },
                                        scales: {
                                            x: { grid: { display: false }, border: { color: t.baseline }, ticks: { color: t.text } },
                                            y: { beginAtZero: true, grid: { color: t.grid }, border: { display: false }, ticks: { color: t.text, maxTicksLimit: 5 } },
                                        },
                                    },
                                });
                            },
                        }"
                    >
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">Cash Flow</p>
                                <p class="mt-0.5 text-xs text-ink-500">Money in vs money out, last 6 months ({{ $currency }})</p>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-medium text-ink-600">
                                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#059669] dark:bg-[#12A372]"></span>Income</span>
                                @if ($expenses)
                                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#C68F00] dark:bg-[#BD8A00]"></span>Expenses</span>
                                @endif
                            </div>
                        </div>
                        <div class="h-64"><canvas x-ref="cashflow" role="img" aria-label="Monthly income and expenses, last 6 months"></canvas></div>
                    </div>
                @endif

                @if ($topOpenInvoices)
                    <div class="panel flex flex-col {{ $income ? 'lg:col-span-4' : 'lg:col-span-12' }}">
                        <div class="flex items-center justify-between border-b border-ink-100 px-6 py-4">
                            <div>
                                <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">Largest Open Invoices</p>
                                <p class="mt-0.5 text-xs text-ink-500">Highest unpaid balances</p>
                            </div>
                            <a href="{{ route('invoices.index') }}" wire:navigate class="text-xs font-semibold text-accent-600 hover:underline">View all</a>
                        </div>
                        <ul class="flex-1 divide-y divide-ink-100">
                            @forelse ($topOpenInvoices as $inv)
                                @php $isOverdue = $inv->due_date && $inv->due_date->lt(today()); @endphp
                                <li>
                                    <a href="{{ route('invoices.show', $inv) }}" wire:navigate class="flex items-center gap-3 px-6 py-3 transition hover:bg-ink-50">
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
                                <li class="px-6 py-10 text-center text-sm text-ink-500">No open invoices — everything billed is collected.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Row 4: operations snapshot --------------------------------------- --}}
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
        @if (count($snapshot))
            <div class="panel mt-5">
                <div class="border-b border-ink-100 px-6 py-4">
                    <p class="text-[13px] font-bold uppercase tracking-wide text-ink-800">Operations Snapshot</p>
                    <p class="mt-0.5 text-xs text-ink-500">Live counts across every module you can access — click any row to open the list.</p>
                </div>
                <div class="grid grid-cols-1 divide-y divide-ink-100 sm:grid-cols-2 sm:divide-y-0 xl:grid-cols-4 xl:divide-x">
                    @foreach ($snapshot as $section => $rows)
                        <div class="p-5">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.08em] text-ink-400">{{ $section }}</p>
                            <ul class="space-y-0.5">
                                @foreach ($rows as $row)
                                    @php [$label, $value, $href] = $row; @endphp
                                    <li>
                                        <a href="{{ $href }}" wire:navigate class="group -mx-2 flex items-center justify-between gap-3 rounded-md px-2 py-1.5 text-sm transition hover:bg-ink-50">
                                            <span class="truncate text-ink-600 group-hover:text-ink-900">{{ $label }}</span>
                                            <span class="min-w-[2.25rem] rounded-md bg-ink-100 px-2 py-0.5 text-center text-sm font-bold tabular-nums text-ink-900 group-hover:bg-accent-100">{{ number_format($value) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Row 5: status breakdowns (single-series bars) ------------------- --}}
        @if ($leadsByStatus !== null || $projectsByStatus !== null || $invoicesByStatus !== null)
            <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
                @php
                    $breakdowns = [
                    ['data' => $leadsByStatus, 'enum' => \App\Enums\LeadStatus::class, 'title' => 'Leads by status', 'unit' => 'Leads', 'horizontal' => true],
                    ['data' => $projectsByStatus, 'enum' => \App\Enums\ProjectStatus::class, 'title' => 'Projects by status', 'unit' => 'Projects', 'horizontal' => true],
                    ['data' => $invoicesByStatus, 'enum' => \App\Enums\InvoiceStatus::class, 'title' => 'Invoices by status', 'unit' => 'Invoices', 'horizontal' => true],
                ];
                @endphp
                @foreach ($breakdowns as $chart)
                    @if ($chart['data'] !== null)
                        @php
                            $labels = collect($chart['enum']::cases())->map(fn ($c) => $c->label())->all();
                            $counts = collect($chart['enum']::cases())->map(fn ($c) => $chart['data'][$c->value] ?? 0)->all();
                        @endphp
                        <div class="panel p-6"
                            x-data="{
                                init() {
                                    const t = window.psChartTheme();
                                    const horizontal = @js($chart['horizontal']);
                                    const valueAxis = { beginAtZero: true, grid: { color: t.grid }, border: { display: false }, ticks: { color: t.text, precision: 0, maxTicksLimit: 5 } };
                                    const catAxis = { grid: { display: false }, border: { color: t.baseline }, ticks: { color: t.text, font: { size: 11 } } };
                                    new window.Chart(this.$refs.canvas, {
                                        type: 'bar',
                                        data: { labels: @js($labels), datasets: [{ label: @js($chart['unit']), data: @js($counts), backgroundColor: t.single, borderRadius: 4, borderSkipped: 'start', maxBarThickness: 18 }] },
                                        options: {
                                            indexAxis: horizontal ? 'y' : 'x',
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { display: false } },
                                            scales: horizontal ? { x: valueAxis, y: catAxis } : { x: catAxis, y: valueAxis },
                                        },
                                    });
                                },
                            }"
                        >
                            <p class="mb-4 text-[13px] font-bold uppercase tracking-wide text-ink-800">{{ $chart['title'] }}</p>
                            <div class="h-60"><canvas x-ref="canvas" role="img" aria-label="{{ $chart['title'] }}"></canvas></div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @endif
</div>
