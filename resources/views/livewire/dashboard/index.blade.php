<div>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp

    <x-page-header
        title="Dashboard"
        description="{{ $greeting }}, {{ \Illuminate\Support\Facades\Auth::user()->name }} — here's {{ config('app.name') }} at a glance on {{ now()->format('l, j M Y') }}."
    />

    @if ($totalLeads === null && $totalClients === null && $upcomingSiteVisits === null && $activeProjects === null && $designsAwaitingDecision === null && $quotationsAwaitingDecision === null && $changeOrdersPending === null && $outstandingInvoicesCount === null && $expensesThisMonth === null && $lowStockProducts === null && $pendingRequisitions === null && $pendingPurchaseOrders === null && $pendingLeaveRequests === null && $payrollDue === null && $openSnags === null && $openServiceRequests === null)
        <x-empty-state
            title="Welcome to {{ config('app.name') }}"
            description="Your account doesn't have access to any CRM modules yet. Ask an administrator to assign you a role."
        />
    @else
        {{-- Hero row: the headline numbers worth seeing before anything else.
             UI/UX redesign round 2 (reference: dexignzone ERES demo) — each
             tile is a solid brand-tone tile with a white icon medallion at
             the right, cycling through the four design-system tones
             (gold/emerald/orange/coral) instead of one repeated gradient,
             so the row itself reads as more varied and "eye catching" at a
             glance — the same move the reference demo makes with its own
             red/green/blue/purple stat row. --}}
        @php
            $heroTones = [
                'accent' => 'from-accent-500 to-accent-600',
                'success' => 'from-success-500 to-success-600',
                'warning' => 'from-warning-500 to-warning-600',
                'danger' => 'from-danger-400 to-danger-500',
            ];
            $heroCards = array_values(array_filter([
                $totalLeads !== null ? ['label' => 'Total Leads', 'value' => $totalLeads, 'icon' => 'funnel', 'href' => route('leads.index'), 'tone' => 'accent'] : null,
                $activeProjects !== null ? ['label' => 'Active Projects', 'value' => $activeProjects, 'icon' => 'briefcase', 'href' => route('projects.index'), 'tone' => 'success'] : null,
                $outstandingInvoicesCount !== null ? ['label' => 'Outstanding Invoices', 'value' => number_format($outstandingInvoicesTotal, 2), 'sub' => $outstandingInvoicesCount.' invoice'.($outstandingInvoicesCount === 1 ? '' : 's'), 'icon' => 'receipt', 'href' => route('invoices.index', ['status' => 'issued']), 'tone' => 'warning'] : null,
                $totalClients !== null ? ['label' => 'Total Clients', 'value' => $totalClients, 'icon' => 'users', 'href' => route('clients.index'), 'tone' => 'danger'] : null,
            ]));
        @endphp

        @if (count($heroCards))
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($heroCards as $hero)
                    <a href="{{ $hero['href'] }}" wire:navigate class="group flex items-center justify-between gap-4 rounded-xl bg-gradient-to-br {{ $heroTones[$hero['tone']] }} p-5 text-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white/80">{{ $hero['label'] }}</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $hero['value'] }}</p>
                            @isset($hero['sub'])
                                <p class="mt-1 text-xs text-white/70">{{ $hero['sub'] }}</p>
                            @endisset
                        </div>
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 ring-1 ring-white/30 transition group-hover:bg-white/25">
                            <x-icon name="{{ $hero['icon'] }}" class="h-6 w-6 text-white" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- CRM & Sales ---------------------------------------------------- --}}
        @if ($newLeads !== null || $openLeads !== null || $upcomingSiteVisits !== null || $designsAwaitingDecision !== null || $quotationsAwaitingDecision !== null || $changeOrdersPending !== null)
            <div class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink-500">
                    <x-icon name="trending-up" class="h-4 w-4 text-accent-600" /> CRM &amp; Sales
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @if ($newLeads !== null)
                        <a href="{{ route('leads.index', ['status' => 'new']) }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">New Leads</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $newLeads }}</p>
                        </a>
                    @endif
                    @if ($openLeads !== null)
                        <div class="panel p-5">
                            <p class="text-sm text-ink-500">Open Leads</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openLeads }}</p>
                        </div>
                    @endif
                    @if ($upcomingSiteVisits !== null)
                        <a href="{{ route('site-visits.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Upcoming Site Visits</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $upcomingSiteVisits }}</p>
                        </a>
                    @endif
                    @if ($designsAwaitingDecision !== null)
                        <a href="{{ route('designs.index', ['status' => 'under_review']) }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Designs Awaiting Decision</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $designsAwaitingDecision }}</p>
                        </a>
                    @endif
                    @if ($quotationsAwaitingDecision !== null)
                        <a href="{{ route('quotations.index', ['status' => 'sent']) }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Quotations Awaiting Decision</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $quotationsAwaitingDecision }}</p>
                        </a>
                    @endif
                    @if ($changeOrdersPending !== null)
                        <a href="{{ route('change-orders.index', ['status' => 'pending']) }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Change Orders Pending</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $changeOrdersPending }}</p>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Projects & Finance ----------------------------------------------- --}}
        @if ($projectsOnHold !== null || $expensesThisMonth !== null)
            <div class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink-500">
                    <x-icon name="wallet" class="h-4 w-4 text-accent-600" /> Projects &amp; Finance
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @if ($projectsOnHold !== null)
                        <a href="{{ route('projects.index', ['status' => 'on_hold']) }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Projects On Hold</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $projectsOnHold }}</p>
                        </a>
                    @endif
                    @if ($expensesThisMonth !== null)
                        <a href="{{ route('expenses.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">This Month's Expenses</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ number_format($expensesThisMonth, 2) }}</p>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Inventory & Procurement ------------------------------------------ --}}
        @if ($lowStockProducts !== null || $pendingRequisitions !== null || $pendingPurchaseOrders !== null)
            <div class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink-500">
                    <x-icon name="warehouse" class="h-4 w-4 text-accent-600" /> Inventory &amp; Procurement
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @if ($lowStockProducts !== null)
                        <a href="{{ route('products.index', ['lowStock' => true]) }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Low Stock Products</p>
                            <p class="mt-2 text-3xl font-semibold {{ $lowStockProducts > 0 ? 'text-danger-600' : 'text-ink-900' }}">{{ $lowStockProducts }}</p>
                        </a>
                    @endif
                    @if ($pendingRequisitions !== null)
                        <a href="{{ route('material-requisitions.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Pending Material Requisitions</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $pendingRequisitions }}</p>
                        </a>
                    @endif
                    @if ($pendingPurchaseOrders !== null)
                        <a href="{{ route('purchase-orders.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Pending Purchase Orders</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $pendingPurchaseOrders }}</p>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- HR & Workforce ----------------------------------------------- --}}
        @if ($pendingLeaveRequests !== null || $payrollDue !== null)
            <div class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink-500">
                    <x-icon name="hard-hat" class="h-4 w-4 text-accent-600" /> HR &amp; Workforce
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @if ($pendingLeaveRequests !== null)
                        <a href="{{ route('leaves.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Pending Leave Requests</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $pendingLeaveRequests }}</p>
                        </a>
                    @endif
                    @if ($payrollDue !== null)
                        <a href="{{ route('payroll.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Payroll Due (this month)</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $payrollDue }}</p>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- QC & Handover ------------------------------------------------- --}}
        @if ($openSnags !== null || $openServiceRequests !== null)
            <div class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink-500">
                    <x-icon name="shield-check" class="h-4 w-4 text-accent-600" /> QC &amp; Handover
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @if ($openSnags !== null)
                        <a href="{{ route('snags.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Open Snags</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openSnags }}</p>
                        </a>
                    @endif
                    @if ($openServiceRequests !== null)
                        <a href="{{ route('warranties.index') }}" wire:navigate class="panel p-5 transition hover:-translate-y-0.5 hover:ring-1 hover:ring-accent-200">
                            <p class="text-sm text-ink-500">Open Service Requests</p>
                            <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openServiceRequests }}</p>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Charts ---------------------------------------------------------- --}}
        @if ($leadsByStatus !== null || $projectsByStatus !== null || $invoicesByStatus !== null)
            <div class="mt-8">
                <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-ink-500">
                    <x-icon name="bar-chart" class="h-4 w-4 text-accent-600" /> Breakdown
                </h2>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    @if ($leadsByStatus !== null)
                        @php
                            $leadStatusLabels = collect(\App\Enums\LeadStatus::cases())->map(fn ($case) => $case->label())->all();
                            $leadStatusCounts = collect(\App\Enums\LeadStatus::cases())->map(fn ($case) => $leadsByStatus[$case->value] ?? 0)->all();
                        @endphp
                        <div
                            class="panel p-5"
                            x-data="{
                                init() {
                                    new window.Chart(this.$refs.leadStatusChart, {
                                        type: 'bar',
                                        data: {
                                            labels: @js($leadStatusLabels),
                                            datasets: [{
                                                label: 'Leads',
                                                data: @js($leadStatusCounts),
                                                backgroundColor: '#D4A000',
                                                borderRadius: 4,
                                            }],
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { display: false } },
                                            scales: {
                                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                            },
                                        },
                                    });
                                },
                            }"
                        >
                            <h3 class="mb-4 text-sm font-semibold text-ink-700">Leads by status</h3>
                            <div class="h-56">
                                <canvas x-ref="leadStatusChart"></canvas>
                            </div>
                        </div>
                    @endif

                    @if ($projectsByStatus !== null)
                        @php
                            $projectStatusLabels = collect(\App\Enums\ProjectStatus::cases())->map(fn ($case) => $case->label())->all();
                            $projectStatusCounts = collect(\App\Enums\ProjectStatus::cases())->map(fn ($case) => $projectsByStatus[$case->value] ?? 0)->all();
                        @endphp
                        <div
                            class="panel p-5"
                            x-data="{
                                init() {
                                    new window.Chart(this.$refs.projectStatusChart, {
                                        type: 'doughnut',
                                        data: {
                                            labels: @js($projectStatusLabels),
                                            datasets: [{
                                                data: @js($projectStatusCounts),
                                                backgroundColor: ['#F4B400','#D4A000','#A67D00','#10B981','#34D399','#FB923C','#EA580C','#7A7468','#059669','#9D9689','#CB3B2B'],
                                                borderWidth: 0,
                                            }],
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
                                        },
                                    });
                                },
                            }"
                        >
                            <h3 class="mb-4 text-sm font-semibold text-ink-700">Projects by status</h3>
                            <div class="h-56">
                                <canvas x-ref="projectStatusChart"></canvas>
                            </div>
                        </div>
                    @endif

                    @if ($invoicesByStatus !== null)
                        @php
                            $invoiceStatusLabels = collect(\App\Enums\InvoiceStatus::cases())->map(fn ($case) => $case->label())->all();
                            $invoiceStatusCounts = collect(\App\Enums\InvoiceStatus::cases())->map(fn ($case) => $invoicesByStatus[$case->value] ?? 0)->all();
                        @endphp
                        <div
                            class="panel p-5"
                            x-data="{
                                init() {
                                    new window.Chart(this.$refs.invoiceStatusChart, {
                                        type: 'bar',
                                        data: {
                                            labels: @js($invoiceStatusLabels),
                                            datasets: [{
                                                label: 'Invoices',
                                                data: @js($invoiceStatusCounts),
                                                backgroundColor: '#059669',
                                                borderRadius: 4,
                                            }],
                                        },
                                        options: {
                                            indexAxis: 'y',
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { display: false } },
                                            scales: {
                                                x: { beginAtZero: true, ticks: { precision: 0 } },
                                            },
                                        },
                                    });
                                },
                            }"
                        >
                            <h3 class="mb-4 text-sm font-semibold text-ink-700">Invoices by status</h3>
                            <div class="h-56">
                                <canvas x-ref="invoiceStatusChart"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @can('view_reports')
            <a href="{{ route('reports.index') }}" wire:navigate class="mt-8 flex items-center justify-between rounded-lg border border-ink-200 bg-surface p-4 text-sm text-ink-700 transition hover:border-accent-300 hover:bg-accent-50">
                <span class="flex items-center gap-2">
                    <x-icon name="file-text" class="h-4 w-4 text-accent-600" />
                    Want the full picture? Open the Reports hub for detailed breakdowns across every module.
                </span>
                <span class="font-medium text-accent-700">View Reports →</span>
            </a>
        @endcan
    @endif
</div>
