<div>
    @if ($totalLeads === null && $totalClients === null && $upcomingSiteVisits === null && $activeProjects === null && $designsAwaitingDecision === null && $quotationsAwaitingDecision === null && $changeOrdersPending === null && $outstandingInvoicesCount === null && $expensesThisMonth === null && $lowStockProducts === null && $pendingRequisitions === null)
        <x-empty-state
            title="Welcome to {{ config('app.name') }}"
            description="Your account doesn't have access to any CRM modules yet. Ask an administrator to assign you a role."
        />
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if ($totalLeads !== null)
                <a href="{{ route('leads.index') }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Total Leads</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalLeads }}</p>
                </a>
                <a href="{{ route('leads.index', ['status' => 'new']) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">New Leads</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $newLeads }}</p>
                </a>
                <div class="card p-5">
                    <p class="text-sm font-medium text-gray-500">Open Leads</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $openLeads }}</p>
                </div>
            @endif

            @if ($totalClients !== null)
                <a href="{{ route('clients.index') }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Total Clients</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalClients }}</p>
                </a>
            @endif

            @if ($upcomingSiteVisits !== null)
                <a href="{{ route('site-visits.index') }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Upcoming Site Visits</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $upcomingSiteVisits }}</p>
                </a>
            @endif

            @if ($activeProjects !== null)
                <a href="{{ route('projects.index') }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Active Projects</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $activeProjects }}</p>
                </a>
                <a href="{{ route('projects.index', ['status' => 'on_hold']) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Projects On Hold</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $projectsOnHold }}</p>
                </a>
            @endif

            @if ($designsAwaitingDecision !== null)
                <a href="{{ route('designs.index', ['status' => 'under_review']) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Designs Awaiting Decision</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $designsAwaitingDecision }}</p>
                </a>
            @endif

            @if ($quotationsAwaitingDecision !== null)
                <a href="{{ route('quotations.index', ['status' => 'sent']) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Quotations Awaiting Decision</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $quotationsAwaitingDecision }}</p>
                </a>
            @endif

            @if ($changeOrdersPending !== null)
                <a href="{{ route('change-orders.index', ['status' => 'pending']) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Change Orders Pending</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $changeOrdersPending }}</p>
                </a>
            @endif

            @if ($outstandingInvoicesCount !== null)
                <a href="{{ route('invoices.index', ['status' => 'issued']) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Outstanding Invoices</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $outstandingInvoicesCount }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ number_format($outstandingInvoicesTotal, 2) }} outstanding</p>
                </a>
            @endif

            @if ($expensesThisMonth !== null)
                <a href="{{ route('expenses.index') }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">This Month's Expenses</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($expensesThisMonth, 2) }}</p>
                </a>
            @endif

            @if ($lowStockProducts !== null)
                <a href="{{ route('products.index', ['lowStock' => true]) }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Low Stock Products</p>
                    <p class="mt-2 text-3xl font-semibold {{ $lowStockProducts > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $lowStockProducts }}</p>
                </a>
            @endif

            @if ($pendingRequisitions !== null)
                <a href="{{ route('material-requisitions.index') }}" wire:navigate class="card p-5 transition hover:shadow-md">
                    <p class="text-sm font-medium text-gray-500">Pending Material Requisitions</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $pendingRequisitions }}</p>
                </a>
            @endif
        </div>

        <p class="mt-6 text-sm text-gray-400">
            Procurement KPIs and charts (Vendors, Purchase Orders, Goods Receives) will appear here as those modules ship in later phases.
        </p>
    @endif
</div>
