<div>
    <x-page-header title="Dashboard" description="Your business at a glance." />

    @if ($totalLeads === null && $totalClients === null && $upcomingSiteVisits === null && $activeProjects === null && $designsAwaitingDecision === null && $quotationsAwaitingDecision === null && $changeOrdersPending === null && $outstandingInvoicesCount === null && $expensesThisMonth === null && $lowStockProducts === null && $pendingRequisitions === null && $pendingPurchaseOrders === null && $pendingLeaveRequests === null && $payrollDue === null && $openSnags === null && $openServiceRequests === null)
        <x-empty-state
            title="Welcome to {{ config('app.name') }}"
            description="Your account doesn't have access to any CRM modules yet. Ask an administrator to assign you a role."
        />
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if ($totalLeads !== null)
                <a href="{{ route('leads.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Total Leads</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $totalLeads }}</p>
                </a>
                <a href="{{ route('leads.index', ['status' => 'new']) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">New Leads</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $newLeads }}</p>
                </a>
                <div class="panel p-5">
                    <p class="text-sm text-ink-500">Open Leads</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openLeads }}</p>
                </div>
            @endif

            @if ($totalClients !== null)
                <a href="{{ route('clients.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Total Clients</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $totalClients }}</p>
                </a>
            @endif

            @if ($upcomingSiteVisits !== null)
                <a href="{{ route('site-visits.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Upcoming Site Visits</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $upcomingSiteVisits }}</p>
                </a>
            @endif

            @if ($activeProjects !== null)
                <a href="{{ route('projects.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Active Projects</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $activeProjects }}</p>
                </a>
                <a href="{{ route('projects.index', ['status' => 'on_hold']) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Projects On Hold</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $projectsOnHold }}</p>
                </a>
            @endif

            @if ($designsAwaitingDecision !== null)
                <a href="{{ route('designs.index', ['status' => 'under_review']) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Designs Awaiting Decision</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $designsAwaitingDecision }}</p>
                </a>
            @endif

            @if ($quotationsAwaitingDecision !== null)
                <a href="{{ route('quotations.index', ['status' => 'sent']) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Quotations Awaiting Decision</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $quotationsAwaitingDecision }}</p>
                </a>
            @endif

            @if ($changeOrdersPending !== null)
                <a href="{{ route('change-orders.index', ['status' => 'pending']) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Change Orders Pending</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $changeOrdersPending }}</p>
                </a>
            @endif

            @if ($outstandingInvoicesCount !== null)
                <a href="{{ route('invoices.index', ['status' => 'issued']) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Outstanding Invoices</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $outstandingInvoicesCount }}</p>
                    <p class="mt-1 text-xs text-ink-400">{{ number_format($outstandingInvoicesTotal, 2) }} outstanding</p>
                </a>
            @endif

            @if ($expensesThisMonth !== null)
                <a href="{{ route('expenses.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">This Month's Expenses</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ number_format($expensesThisMonth, 2) }}</p>
                </a>
            @endif

            @if ($lowStockProducts !== null)
                <a href="{{ route('products.index', ['lowStock' => true]) }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Low Stock Products</p>
                    <p class="mt-2 text-3xl font-semibold {{ $lowStockProducts > 0 ? 'text-danger-600' : 'text-ink-900' }}">{{ $lowStockProducts }}</p>
                </a>
            @endif

            @if ($pendingRequisitions !== null)
                <a href="{{ route('material-requisitions.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Pending Material Requisitions</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $pendingRequisitions }}</p>
                </a>
            @endif

            @if ($pendingPurchaseOrders !== null)
                <a href="{{ route('purchase-orders.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Pending Purchase Orders</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $pendingPurchaseOrders }}</p>
                </a>
            @endif

            @if ($pendingLeaveRequests !== null)
                <a href="{{ route('leaves.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Pending Leave Requests</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $pendingLeaveRequests }}</p>
                </a>
            @endif

            @if ($payrollDue !== null)
                <a href="{{ route('payroll.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Payroll Due (this month)</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $payrollDue }}</p>
                </a>
            @endif

            @if ($openSnags !== null)
                <a href="{{ route('snags.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Open Snags</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openSnags }}</p>
                </a>
            @endif

            @if ($openServiceRequests !== null)
                <a href="{{ route('warranties.index') }}" wire:navigate class="panel p-5 transition hover:border-accent-300">
                    <p class="text-sm text-ink-500">Open Service Requests</p>
                    <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openServiceRequests }}</p>
                </a>
            @endif
        </div>

        @if ($leadsByStatus !== null)
            @php
                $leadStatusLabels = collect(\App\Enums\LeadStatus::cases())->map(fn ($case) => $case->label())->all();
                $leadStatusCounts = collect(\App\Enums\LeadStatus::cases())->map(fn ($case) => $leadsByStatus[$case->value] ?? 0)->all();
            @endphp
            <div
                class="panel mt-6 p-5"
                x-data="{
                    init() {
                        new window.Chart(this.$refs.leadStatusChart, {
                            type: 'bar',
                            data: {
                                labels: @js($leadStatusLabels),
                                datasets: [{
                                    label: 'Leads',
                                    data: @js($leadStatusCounts),
                                    backgroundColor: '#2B6157',
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
                <h2 class="mb-4 text-sm font-semibold text-ink-700">Leads by status</h2>
                <div class="h-64">
                    <canvas x-ref="leadStatusChart"></canvas>
                </div>
            </div>
        @endif

        <p class="mt-6 text-sm text-ink-400">
            Reports, Notifications, Client Portal and Vendor Portal will appear here as those modules ship in later phases.
        </p>
    @endif
</div>
