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
    Brand-layout pass: a LIGHT sidebar modeled on the reference finance-
    dashboard layout — white rail with a hairline right border, brand mark
    + company name at the top, a desktop collapse toggle (icon-only rail,
    remembered in localStorage), and the active item marked with the PS
    Solution gold: a 3px gold edge bar, a faint gold wash, and charcoal
    semibold text. Uses the theme-variable `surface`/`ink-*`/`accent-*`
    tokens again (unlike the previous always-dark rail), so it follows the
    day/night toggle with the rest of the app. The $groups data,
    permission gating, active-route logic and per-group open/close
    persistence are unchanged — only presentation and the collapse toggle
    are new.
--}}
<aside
    x-data="{
        collapsed: false,
        init() {
            try { this.collapsed = localStorage.getItem('sidebar-collapsed') === '1'; } catch (e) {}
        },
        toggleCollapsed() {
            this.collapsed = ! this.collapsed;
            try { localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0'); } catch (e) {}
        },
    }"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        collapsed ? 'lg:w-[76px]' : 'lg:w-64',
    ]"
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-ink-200 bg-surface transition-all duration-200 ease-in-out lg:static lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center gap-3 px-5" :class="collapsed ? 'lg:justify-center lg:px-0' : ''">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-500 text-sm font-extrabold text-charcoal">
            {{ strtoupper(substr(\App\Models\Setting::get('company.name', config('app.name')), 0, 2)) }}
        </div>
        <div class="min-w-0 flex-1" :class="collapsed ? 'lg:hidden' : ''">
            <p class="truncate text-[15px] font-extrabold tracking-tight text-ink-900">{{ \App\Models\Setting::get('company.name', config('app.name')) }}</p>
            <p class="truncate text-[11px] font-medium text-ink-400">Enterprise ERP</p>
        </div>
        <button
            type="button"
            @click="toggleCollapsed()"
            class="hidden rounded-md p-1.5 text-ink-400 transition hover:bg-ink-100 hover:text-ink-700 lg:block"
            :class="collapsed ? 'lg:hidden' : ''"
            aria-label="Collapse sidebar"
        >
            <x-icon name="panel-left-close" class="h-[18px] w-[18px]" />
        </button>
    </div>

    <button
        type="button"
        x-show="collapsed"
        x-cloak
        @click="toggleCollapsed()"
        class="mx-auto mb-2 hidden rounded-md p-1.5 text-ink-400 transition hover:bg-ink-100 hover:text-ink-700 lg:block"
        aria-label="Expand sidebar"
    >
        <x-icon name="panel-left-open" class="h-[18px] w-[18px]" />
    </button>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4 pt-2">
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
                            // A stored preference wins over the server-
                            // computed default (auto-expand the group that
                            // holds the current route); survives
                            // wire:navigate since localStorage persists.
                            try {
                                const stored = localStorage.getItem(this.storageKey);
                                if (stored !== null) {
                                    this.open = JSON.parse(stored);
                                }
                            } catch (e) {}
                        },
                        toggle() {
                            this.open = ! this.open;
                            try {
                                localStorage.setItem(this.storageKey, JSON.stringify(this.open));
                            } catch (e) {}
                        },
                    }"
                >
                    <button
                        type="button"
                        @click="toggle()"
                        x-show="! collapsed"
                        class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-3 text-[11px] font-bold uppercase tracking-[0.08em] text-ink-400 transition hover:text-ink-700"
                    >
                        <span>{{ $group['label'] }}</span>
                        <x-icon
                            name="chevron-down"
                            class="h-3.5 w-3.5 shrink-0 transition-transform duration-150"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>
                    <div x-show="collapsed" x-cloak class="mx-auto my-2 hidden h-px w-8 bg-ink-200 lg:block"></div>

                    <div x-show="open || collapsed" x-cloak class="space-y-0.5">
                        @foreach ($visibleItems as $item)
                            @php $isActive = request()->routeIs($item['active'] ?? $item['route'].'*'); @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                title="{{ $item['label'] }}"
                                @class([
                                    'relative flex items-center gap-3 rounded-lg px-3 py-2 text-[13.5px] transition-colors',
                                    'bg-accent-50 font-semibold text-ink-900' => $isActive,
                                    'font-medium text-ink-600 hover:bg-ink-50 hover:text-ink-900' => ! $isActive,
                                ])
                                :class="collapsed ? 'lg:justify-center lg:px-0' : ''"
                            >
                                @if ($isActive)
                                    <span class="absolute inset-y-1.5 left-0 w-[3px] rounded-r-full bg-accent-500"></span>
                                @endif
                                <x-icon
                                    :name="$item['icon']"
                                    @class([
                                        'h-[18px] w-[18px] shrink-0',
                                        'text-accent-600' => $isActive,
                                        'text-ink-400' => ! $isActive,
                                    ])
                                />
                                <span class="truncate" :class="collapsed ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-ink-200 p-3">
        <div class="flex items-center gap-3 rounded-lg p-2" :class="collapsed ? 'lg:justify-center lg:p-0 lg:py-2' : ''">
            <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-charcoal text-xs font-bold text-accent-500 dark:bg-accent-500 dark:text-charcoal">
                {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-success-500 ring-2 ring-surface"></span>
            </div>
            <div class="min-w-0 flex-1" :class="collapsed ? 'lg:hidden' : ''">
                <p class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-ink-500">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" :class="collapsed ? 'lg:hidden' : ''">
                @csrf
                <button type="submit" title="Log out" class="rounded-md p-1.5 text-ink-400 transition hover:bg-danger-50 hover:text-danger-600" aria-label="Log out">
                    <x-icon name="log-out" class="h-[18px] w-[18px]" />
                </button>
            </form>
        </div>
    </div>
</aside>
