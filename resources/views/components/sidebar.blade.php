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
    Soft-canvas pass: a floating rounded "pill" navigation rail, modeled on
    the reference analytics layout — icon-only by default on desktop, each
    item a 44px circle with the active page in a solid charcoal circle and
    a gold icon (gold circle + charcoal icon in dark mode), the PS Solution
    pair. Hovering an icon shows its label in a tooltip (teleported to
    <body> so the rail's scroll area can't clip it). The toggle at the top
    expands the rail to show labels and the collapsible module groups;
    the choice is remembered in localStorage ('sidebar-collapsed'). On
    mobile it is an off-canvas drawer that always shows labels.

    The $groups data, permission gating, active-route logic and per-group
    open/close persistence are unchanged — only presentation changed.
--}}
<aside
    x-data="{
        collapsed: true,
        desktop: window.matchMedia('(min-width: 1024px)').matches,
        tip: { show: false, label: '', top: 0, left: 0 },
        get rail() { return this.collapsed && this.desktop; },
        init() {
            try {
                const stored = localStorage.getItem('sidebar-collapsed');
                if (stored !== null) this.collapsed = stored === '1';
            } catch (e) {}
            const mq = window.matchMedia('(min-width: 1024px)');
            mq.addEventListener('change', (e) => { this.desktop = e.matches; this.tip.show = false; });
        },
        toggleCollapsed() {
            this.collapsed = ! this.collapsed;
            this.tip.show = false;
            try { localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0'); } catch (e) {}
        },
        showTip(el, label) {
            if (! this.rail) return;
            const r = el.getBoundingClientRect();
            this.tip = { show: true, label, top: r.top + r.height / 2, left: r.right + 12 };
        },
    }"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-[110%] lg:translate-x-0',
        rail ? 'lg:w-[72px]' : 'lg:w-64',
    ]"
    class="fixed inset-y-3 left-3 z-40 flex w-72 shrink-0 flex-col rounded-[32px] border border-ink-200/70 bg-surface shadow-2xl shadow-black/10 transition-all duration-200 ease-in-out dark:border-white/[0.06] lg:static lg:inset-auto lg:h-full lg:shadow-[0_1px_2px_rgba(32,36,31,0.04)]"
>
    {{-- Expand / collapse (desktop) and close (mobile) --}}
    <div class="flex shrink-0 items-center gap-2 px-3 pb-2 pt-3" :class="rail ? 'justify-center' : 'justify-between pl-5'">
        <span x-show="! rail" class="text-[11px] font-bold uppercase tracking-[0.12em] text-ink-400">Menu</span>
        <button
            type="button"
            @click="toggleCollapsed()"
            class="hidden h-11 w-11 items-center justify-center rounded-full text-ink-500 transition hover:bg-ink-100 hover:text-ink-900 lg:flex"
            :aria-label="rail ? 'Expand sidebar' : 'Collapse sidebar'"
            @mouseenter="showTip($el, 'Expand menu')" @mouseleave="tip.show = false"
        >
            <x-icon name="panel-left-open" class="h-[18px] w-[18px]" x-show="rail" />
            <x-icon name="panel-left-close" class="h-[18px] w-[18px]" x-show="! rail" x-cloak />
        </button>
        <button type="button" @click="sidebarOpen = false" class="flex h-10 w-10 items-center justify-center rounded-full text-ink-500 transition hover:bg-ink-100 lg:hidden" aria-label="Close menu">
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto overscroll-contain px-3 pb-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" @scroll="tip.show = false">
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
                    class="py-0.5"
                >
                    <button
                        type="button"
                        @click="toggle()"
                        x-show="! rail"
                        class="flex w-full items-center justify-between rounded-full px-3 pb-1.5 pt-3 text-[11px] font-bold uppercase tracking-[0.1em] text-ink-400 transition hover:text-ink-700"
                    >
                        <span>{{ $group['label'] }}</span>
                        <x-icon
                            name="chevron-down"
                            class="h-3.5 w-3.5 shrink-0 transition-transform duration-150"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>
                    @unless ($loop->first)
                        <div x-show="rail" x-cloak class="mx-auto my-2 h-1 w-1 rounded-full bg-ink-300"></div>
                    @endunless

                    <div x-show="open || rail" x-cloak class="space-y-1">
                        @foreach ($visibleItems as $item)
                            @php $isActive = request()->routeIs($item['active'] ?? $item['route'].'*'); @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                aria-label="{{ $item['label'] }}"
                                @if ($isActive) aria-current="page" @endif
                                @mouseenter="showTip($el, @js($item['label']))"
                                @mouseleave="tip.show = false"
                                @class([
                                    'group/nav flex items-center rounded-full text-[13.5px] transition-colors',
                                    'bg-charcoal font-semibold text-white dark:bg-accent-500 dark:text-charcoal' => $isActive,
                                    'font-medium text-ink-600 hover:bg-ink-100 hover:text-ink-900' => ! $isActive,
                                ])
                                :class="rail ? 'mx-auto h-11 w-11 justify-center' : 'gap-3 px-3.5 py-2.5'"
                            >
                                <x-icon
                                    :name="$item['icon']"
                                    @class([
                                        'h-[18px] w-[18px] shrink-0',
                                        'text-accent-500 dark:text-charcoal' => $isActive,
                                        'text-ink-400 group-hover/nav:text-ink-700' => ! $isActive,
                                    ])
                                />
                                <span class="truncate" x-show="! rail">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- Appearance toggle, in the brand gold (the reference's accent circle). --}}
    <div class="shrink-0 p-3" :class="rail ? 'flex justify-center' : ''">
        <div class="flex items-center gap-3" :class="rail ? '' : 'rounded-full bg-ink-50 p-1.5 pr-4'">
            <x-theme-toggle class="!h-11 !w-11 !border-0 !bg-accent-500 !text-charcoal hover:!bg-accent-400" />
            <span x-show="! rail" class="text-sm font-medium text-ink-600">Appearance</span>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-show="tip.show && rail"
            x-cloak
            x-transition.opacity.duration.100ms
            :style="`top: ${tip.top}px; left: ${tip.left}px`"
            class="pointer-events-none fixed z-[60] -translate-y-1/2 whitespace-nowrap rounded-full bg-charcoal px-3 py-1.5 text-xs font-semibold text-white shadow-lg dark:bg-accent-500 dark:text-charcoal"
            x-text="tip.label"
        ></div>
    </template>
</aside>
