@php
    // Phase 14: the flat 32-item $menu list from Phases 2-13 is reorganized
    // into collapsible groups so the sidebar stays scannable as modules
    // keep being added. Each group is ['label' => ..., 'items' => [...]];
    // each item keeps the exact same shape it always had — label, route,
    // optional active pattern, icon, optional permission — and the
    // active-item highlighting / permission-gating logic below is
    // unchanged from before, just nested one level deeper. A menu item
    // with no 'permission' key is always shown (e.g. Dashboard). Later
    // phases append their own module's entries to the matching group (or
    // add a new group) — this component itself never changes beyond
    // that.
    $groups = [
        [
            'label' => 'Overview',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
                // No 'permission' key — like Dashboard, every source on
                // the page is gated individually inside Calendar\Index,
                // so the nav entry itself stays visible to any staff user.
                ['label' => 'Calendar', 'route' => 'calendar.index', 'icon' => 'calendar'],
            ],
        ],
        [
            'label' => 'My Workspace',
            'items' => [
                // Self-service — no 'permission' key, same reasoning as
                // Dashboard/Calendar above: any staff login can reach
                // these, authorization happens inside the component
                // against "is this your own Employee record" (see
                // MyLeaves\Index's / MyExpenseClaims\Index's docblocks),
                // not a granular view_*/create_* ability.
                ['label' => 'My Leaves', 'route' => 'my-leaves.index', 'active' => 'my-leaves.*', 'icon' => 'calendar-off'],
                ['label' => 'My Expense Claims', 'route' => 'my-expense-claims.index', 'active' => 'my-expense-claims.*', 'icon' => 'map-pin'],
            ],
        ],
        [
            'label' => 'CRM & Sales',
            'items' => [
                ['label' => 'Leads', 'route' => 'leads.index', 'active' => 'leads.*', 'icon' => 'funnel', 'permission' => 'view_leads'],
                ['label' => 'Site Visits', 'route' => 'site-visits.index', 'active' => 'site-visits.*', 'icon' => 'map-pin', 'permission' => 'view_site_visits'],
                ['label' => 'Clients', 'route' => 'clients.index', 'active' => 'clients.*', 'icon' => 'users', 'permission' => 'view_clients'],
                ['label' => 'Meetings', 'route' => 'meetings.index', 'active' => 'meetings.*', 'icon' => 'calendar', 'permission' => 'view_meetings'],
                ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => 'quotations.*', 'icon' => 'file-text', 'permission' => 'view_quotations'],
                ['label' => 'Contracts', 'route' => 'contracts.index', 'active' => 'contracts.*', 'icon' => 'file-signature', 'permission' => 'view_contracts'],
                ['label' => 'Change Orders', 'route' => 'change-orders.index', 'active' => 'change-orders.*', 'icon' => 'git-pull-request', 'permission' => 'view_change_orders'],
            ],
        ],
        [
            'label' => 'Projects',
            'items' => [
                // Milestones/Tasks/Team stay inline within Project Show —
                // there are no dedicated index routes for them, so no
                // separate nav entries here (same off-nav reasoning as
                // the rest of this file).
                ['label' => 'Projects', 'route' => 'projects.index', 'active' => 'projects.*', 'icon' => 'briefcase', 'permission' => 'view_projects'],
            ],
        ],
        [
            'label' => 'Design Studio',
            'items' => [
                ['label' => 'Designs', 'route' => 'designs.index', 'active' => 'designs.*', 'icon' => 'palette', 'permission' => 'view_designs'],
                ['label' => 'Material Selections', 'route' => 'material-selections.index', 'active' => 'material-selections.*', 'icon' => 'tag', 'permission' => 'view_material_selections'],
            ],
        ],
        [
            'label' => 'Estimation & Billing',
            'items' => [
                ['label' => 'BOQs', 'route' => 'boqs.index', 'active' => 'boqs.*', 'icon' => 'calculator', 'permission' => 'view_boqs'],
                ['label' => 'Invoices', 'route' => 'invoices.index', 'active' => 'invoices.*', 'icon' => 'receipt', 'permission' => 'view_invoices'],
                ['label' => 'Payments', 'route' => 'payments.index', 'active' => 'payments.*', 'icon' => 'credit-card', 'permission' => 'view_payments'],
                ['label' => 'Profitability', 'route' => 'profitability.index', 'icon' => 'trending-up', 'permission' => 'view_finance'],
            ],
        ],
        [
            'label' => 'Procurement',
            'items' => [
                // Vendor Quotations and Goods Receives have no index
                // route — reached only from a Purchase Request's /
                // Purchase Order's Show page — so they don't get a
                // standalone entry here (it would be a dead-feeling link).
                ['label' => 'Vendors', 'route' => 'vendors.index', 'active' => 'vendors.*', 'icon' => 'truck', 'permission' => 'view_vendors'],
                ['label' => 'Purchase Requests', 'route' => 'purchase-requests.index', 'active' => 'purchase-requests.*', 'icon' => 'file-plus', 'permission' => 'view_purchase_orders'],
                ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'active' => 'purchase-orders.*', 'icon' => 'shopping-cart', 'permission' => 'view_purchase_orders'],
                ['label' => 'Vendor Invoices', 'route' => 'vendor-invoices.index', 'active' => 'vendor-invoices.*', 'icon' => 'receipt', 'permission' => 'view_vendor_invoices'],
            ],
        ],
        [
            'label' => 'Inventory & Warehouse',
            'items' => [
                ['label' => 'Products', 'route' => 'products.index', 'active' => 'products.*', 'icon' => 'package', 'permission' => 'view_inventory'],
                ['label' => 'Product Categories', 'route' => 'product-categories.index', 'active' => 'product-categories.*', 'icon' => 'tag', 'permission' => 'manage_settings'],
                ['label' => 'Units', 'route' => 'units.index', 'active' => 'units.*', 'icon' => 'ruler', 'permission' => 'manage_settings'],
                ['label' => 'Warehouses', 'route' => 'warehouses.index', 'active' => 'warehouses.*', 'icon' => 'warehouse', 'permission' => 'view_warehouses'],
                ['label' => 'Stock Movements', 'route' => 'stock-movements.index', 'icon' => 'list', 'permission' => 'view_inventory'],
                ['label' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'active' => 'stock-transfers.*', 'icon' => 'arrow-left-right', 'permission' => 'view_warehouses'],
                ['label' => 'Material Requisitions', 'route' => 'material-requisitions.index', 'active' => 'material-requisitions.*', 'icon' => 'clipboard-list', 'permission' => 'view_material_requisitions'],
            ],
        ],
        [
            'label' => 'Finance',
            'items' => [
                ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => 'expenses.*', 'icon' => 'wallet', 'permission' => 'view_expenses'],
                ['label' => 'Expense Categories', 'route' => 'expense-categories.index', 'active' => 'expense-categories.*', 'icon' => 'folder', 'permission' => 'manage_settings'],
            ],
        ],
        [
            'label' => 'Workforce',
            'items' => [
                // Subcontractor Assignments have no index route —
                // reached from a Subcontractor's Show page — so no
                // standalone entry here.
                ['label' => 'Employees', 'route' => 'employees.index', 'active' => 'employees.*', 'icon' => 'users', 'permission' => 'view_employees'],
                ['label' => 'Departments', 'route' => 'departments.index', 'active' => 'departments.*', 'icon' => 'building', 'permission' => 'manage_employees'],
                ['label' => 'Designations', 'route' => 'designations.index', 'active' => 'designations.*', 'icon' => 'id-badge', 'permission' => 'manage_employees'],
                ['label' => 'Attendance', 'route' => 'attendance.index', 'active' => 'attendance.*', 'icon' => 'clock', 'permission' => 'view_attendance'],
                ['label' => 'Leaves', 'route' => 'leaves.index', 'active' => 'leaves.*', 'icon' => 'calendar-off', 'permission' => 'view_leaves'],
                ['label' => 'Expense Claims', 'route' => 'expense-claims.index', 'active' => 'expense-claims.*', 'icon' => 'wallet', 'permission' => 'view_expense_claims'],
                ['label' => 'Payroll', 'route' => 'payroll.index', 'active' => 'payroll.*', 'icon' => 'banknote', 'permission' => 'view_payroll'],
                ['label' => 'Subcontractors', 'route' => 'subcontractors.index', 'active' => 'subcontractors.*', 'icon' => 'hard-hat', 'permission' => 'view_subcontractors'],
            ],
        ],
        [
            'label' => 'Site Operations & Completion',
            'items' => [
                // Handovers and Service Requests have no index route —
                // reached from a Project's / Warranty's Show page — so
                // no standalone entry here.
                ['label' => 'Site Reports', 'route' => 'site-reports.index', 'active' => 'site-reports.*', 'icon' => 'file-plus', 'permission' => 'view_site_reports'],
                ['label' => 'Inspections', 'route' => 'inspections.index', 'active' => 'inspections.*', 'icon' => 'clipboard-check', 'permission' => 'view_inspections'],
                ['label' => 'Snags', 'route' => 'snags.index', 'active' => 'snags.*', 'icon' => 'alert-triangle', 'permission' => 'view_snags'],
                ['label' => 'Warranties', 'route' => 'warranties.index', 'active' => 'warranties.*', 'icon' => 'shield-check', 'permission' => 'view_warranties'],
            ],
        ],
        [
            'label' => 'Reports',
            'items' => [
                // The 5 sub-reports are reached from within the hub, not
                // given their own top-level entries.
                ['label' => 'Reports', 'route' => 'reports.index', 'active' => 'reports.*', 'icon' => 'bar-chart', 'permission' => 'view_reports'],
            ],
        ],
        [
            'label' => 'System',
            'items' => [
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'permission' => 'manage_settings'],
                // Closes the "User management" / "Role & permission
                // management" audit gap — both use the `users`/`roles`
                // CRUD permissions RolePermissionSeeder has generated
                // since Phase 1 but no role was granted until now
                // (only Admin/Super Admin by default — see
                // UserPolicy/RolePolicy docblocks).
                ['label' => 'Users', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'users', 'permission' => 'view_users'],
                ['label' => 'Roles', 'route' => 'roles.index', 'active' => 'roles.*', 'icon' => 'shield', 'permission' => 'view_roles'],
                // Phase 14: backend (ActivityLogService + LogsActivity
                // trait) has existed since early phases with zero UI —
                // see App\Livewire\ActivityLogs\Index's docblock for the
                // `view_reports` gate reasoning.
                ['label' => 'Audit Log', 'route' => 'audit-log.index', 'icon' => 'history', 'permission' => 'view_reports'],
            ],
        ],
    ];
@endphp

{{--
    Enterprise ERP redesign pass: a persistent DARK navigation rail —
    the clearest single visual signature that separates "ERP" from
    "consumer SaaS" (SAP Fiori's launchpad, Odoo's backend, Salesforce
    Lightning's app rail, Oracle Cloud all use a fixed dark/colored nav
    against a light content canvas). Deliberately hardcoded to literal
    `slate-*`/`white` classes rather than the `ink-*`/`surface` design
    tokens used everywhere else in the app: those tokens are CSS-
    variable-backed so they flip with the day/night theme toggle
    (resources/js/theme.js), but this sidebar is NOT meant to flip — it
    stays dark regardless of whether the content area is in light or
    dark mode, exactly like the ERPs referenced above. `accent-*` classes
    are untouched (accent/success/warning/danger are static hex, never
    theme-variable, so they already read correctly on a dark surface).
    The $groups data, permission gating, active-route logic and Alpine
    behavior are byte-for-byte unchanged across all of this —
    only presentational classes changed.
--}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-slate-800 px-6">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent-600 text-sm font-bold text-white">
            {{ substr(config('app.name'), 0, 1) }}
        </div>
        <span class="truncate text-sm font-semibold tracking-tight text-white">{{ config('app.name') }}</span>
    </div>

    <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
        @foreach ($groups as $group)
            @php
                $visibleItems = array_values(array_filter(
                    $group['items'],
                    fn ($item) => ! isset($item['permission']) || auth()->user()?->can($item['permission'])
                ));

                $groupIsActive = collect($visibleItems)->contains(
                    fn ($item) => request()->routeIs($item['active'] ?? $item['route'].'*')
                );

                $storageKey = 'sidebar-group-open:'.\Illuminate\Support\Str::slug($group['label']);
            @endphp

            @if (count($visibleItems))
                <div
                    x-data="{
                        open: {{ $groupIsActive ? 'true' : 'false' }},
                        storageKey: '{{ $storageKey }}',
                        init() {
                            // Auto-expand the group containing the current
                            // route (the server-computed default above),
                            // unless the visitor already has a stored
                            // preference for this group — that preference
                            // wins and survives wire:navigate since
                            // localStorage isn't reset by Livewire's SPA
                            // navigation (it only re-runs Alpine's x-data
                            // init, it doesn't clear the browser's storage).
                            try {
                                const stored = localStorage.getItem(this.storageKey);
                                if (stored !== null) {
                                    this.open = JSON.parse(stored);
                                }
                            } catch (e) {
                                // Private browsing / disabled storage —
                                // fall back to the server-computed default
                                // (already set above) and carry on.
                            }
                        },
                        toggle() {
                            this.open = ! this.open;
                            try {
                                localStorage.setItem(this.storageKey, JSON.stringify(this.open));
                            } catch (e) {
                                // Storage unavailable — the toggle still
                                // works for this page view via the Alpine
                                // state above, it just won't persist.
                            }
                        },
                    }"
                >
                    <button
                        type="button"
                        @click="toggle()"
                        class="flex w-full items-center justify-between rounded-md px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500 transition hover:bg-slate-800 hover:text-slate-300"
                    >
                        <span>{{ $group['label'] }}</span>
                        <x-icon
                            name="chevron-down"
                            class="h-3.5 w-3.5 shrink-0 transition-transform duration-150"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>

                    <div x-show="open" x-cloak class="space-y-0.5 pb-1">
                        @foreach ($visibleItems as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                {{-- Active item: a solid indigo fill — the
                                     one spot of color against the dark
                                     rail, unmissable at a glance. Inactive:
                                     muted slate text, lighting up to white
                                     on hover. --}}
                                @class([
                                    'flex items-center gap-3 px-3 py-2.5 text-sm font-medium transition-colors',
                                    'rounded-lg bg-accent-600 text-white' => request()->routeIs($item['active'] ?? $item['route'].'*'),
                                    'rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($item['active'] ?? $item['route'].'*'),
                                ])
                            >
                                <x-icon
                                    :name="$item['icon']"
                                    @class([
                                        'h-[18px] w-[18px] shrink-0',
                                        'text-white' => request()->routeIs($item['active'] ?? $item['route'].'*'),
                                        'text-slate-500' => ! request()->routeIs($item['active'] ?? $item['route'].'*'),
                                    ])
                                />
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-slate-800 p-3">
        <div class="flex items-center gap-3 rounded-lg p-2 transition hover:bg-slate-800">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-semibold text-white">
                {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-400">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-0.5">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-red-400">
                Log out
            </button>
        </form>
    </div>
</aside>
