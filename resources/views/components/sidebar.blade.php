@php
    // Each entry: label, route name, icon (lucide name), and the
    // permission that must be present for it to render. A menu item with
    // no 'permission' key is always shown (e.g. Dashboard). Later phases
    // append their own module's entries to this array — the component
    // itself never changes, only this list grows (spec §3: never hard-code
    // permissions inside a controller; the sidebar follows the same rule).
    $menu = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Leads', 'route' => 'leads.index', 'active' => 'leads.*', 'icon' => 'funnel', 'permission' => 'view_leads'],
        ['label' => 'Clients', 'route' => 'clients.index', 'active' => 'clients.*', 'icon' => 'users', 'permission' => 'view_clients'],
        ['label' => 'Site Visits', 'route' => 'site-visits.index', 'active' => 'site-visits.*', 'icon' => 'map-pin', 'permission' => 'view_site_visits'],
        ['label' => 'Projects', 'route' => 'projects.index', 'active' => 'projects.*', 'icon' => 'briefcase', 'permission' => 'view_projects'],
        ['label' => 'Designs', 'route' => 'designs.index', 'active' => 'designs.*', 'icon' => 'palette', 'permission' => 'view_designs'],
        ['label' => 'BOQs', 'route' => 'boqs.index', 'active' => 'boqs.*', 'icon' => 'calculator', 'permission' => 'view_boqs'],
        ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => 'quotations.*', 'icon' => 'file-text', 'permission' => 'view_quotations'],
        ['label' => 'Contracts', 'route' => 'contracts.index', 'active' => 'contracts.*', 'icon' => 'file-signature', 'permission' => 'view_contracts'],
        ['label' => 'Change Orders', 'route' => 'change-orders.index', 'active' => 'change-orders.*', 'icon' => 'git-pull-request', 'permission' => 'view_change_orders'],
        ['label' => 'Invoices', 'route' => 'invoices.index', 'active' => 'invoices.*', 'icon' => 'receipt', 'permission' => 'view_invoices'],
        ['label' => 'Payments', 'route' => 'payments.index', 'active' => 'payments.*', 'icon' => 'credit-card', 'permission' => 'view_payments'],
        ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => 'expenses.*', 'icon' => 'wallet', 'permission' => 'view_expenses'],
        ['label' => 'Profitability', 'route' => 'profitability.index', 'icon' => 'trending-up', 'permission' => 'view_finance'],
        // Phase 7: Inventory, Warehouses, Stock, Material Requisitions.
        // Product Categories, Units and the Stock Movements ledger are
        // reached from the Products index / a product's Show page rather
        // than getting their own top-level entries, keeping this list
        // from growing one item per reference table.
        ['label' => 'Products', 'route' => 'products.index', 'active' => 'products.*', 'icon' => 'package', 'permission' => 'view_inventory'],
        ['label' => 'Warehouses', 'route' => 'warehouses.index', 'active' => 'warehouses.*', 'icon' => 'warehouse', 'permission' => 'view_warehouses'],
        ['label' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'active' => 'stock-transfers.*', 'icon' => 'arrow-left-right', 'permission' => 'view_warehouses'],
        ['label' => 'Material Requisitions', 'route' => 'material-requisitions.index', 'active' => 'material-requisitions.*', 'icon' => 'clipboard-list', 'permission' => 'view_material_requisitions'],
        // Phase 8: Vendors, Procurement (Purchase Requests -> Vendor
        // Quotations -> Purchase Orders), Goods Receives. Purchase
        // Requests/Vendor Quotations are gated on view_purchase_orders —
        // see PurchaseRequestPolicy. Vendor Quotations and Goods
        // Receives are reached from a Purchase Request's / Purchase
        // Order's Show page rather than getting their own top-level
        // entry, keeping this list from growing one item per sub-step.
        ['label' => 'Vendors', 'route' => 'vendors.index', 'active' => 'vendors.*', 'icon' => 'truck', 'permission' => 'view_vendors'],
        ['label' => 'Purchase Requests', 'route' => 'purchase-requests.index', 'active' => 'purchase-requests.*', 'icon' => 'file-plus', 'permission' => 'view_purchase_orders'],
        ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'active' => 'purchase-orders.*', 'icon' => 'shopping-cart', 'permission' => 'view_purchase_orders'],
        // Phase 9+: Employees, Attendance, Leave, Payroll, Subcontractors...
        ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'permission' => 'manage_settings'],
    ];
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-gray-900 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center gap-2 px-6">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">
            {{ substr(config('app.name'), 0, 1) }}
        </div>
        <span class="truncate text-sm font-semibold text-white">{{ config('app.name') }}</span>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($menu as $item)
            @if (! isset($item['permission']) || auth()->user()?->can($item['permission']))
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                        'bg-gray-800 text-white' => request()->routeIs($item['active'] ?? $item['route'].'*'),
                        'text-gray-300 hover:bg-gray-800 hover:text-white' => ! request()->routeIs($item['active'] ?? $item['route'].'*'),
                    ])
                >
                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-gray-800 p-3">
        <div class="flex items-center gap-3 rounded-lg px-3 py-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-700 text-xs font-medium text-white">
                {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-400">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white">
                Log out
            </button>
        </form>
    </div>
</aside>
