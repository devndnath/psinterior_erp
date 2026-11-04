@php
    // Menu order follows the firm's previous system: Dashboard, then a
    // CRM section, a Project section (procurement, vendors, contractors,
    // projects), Accounts and HR, and a Report section — with each module
    // a dropdown of its pages. $sections = [['label' => ?string,
    // 'entries' => [...]]]; an entry is either a link (label, route,
    // optional active pattern, icon, optional permission — a link with no
    // 'permission' is always shown) or a dropdown (label, icon,
    // 'children' => [links]). A dropdown only shows when at least one of
    // its children is visible to the user, and a section only shows when
    // it has a visible entry. No standalone task pages exist (tasks live
    // inside each project), so there is no Task menu.
    $sections = [
        [
            'label' => null,
            'entries' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
                // Every source on the calendar is gated inside Calendar\Index.
                ['label' => 'Calendar', 'route' => 'calendar.index', 'icon' => 'calendar'],
            ],
        ],
        [
            'label' => 'CRM Section',
            'entries' => [
                ['label' => 'CRM', 'icon' => 'funnel', 'children' => [
                    ['label' => 'Leads', 'route' => 'leads.index', 'active' => 'leads.*', 'icon' => 'funnel', 'permission' => 'view_leads'],
                    ['label' => 'Site Visits', 'route' => 'site-visits.index', 'active' => 'site-visits.*', 'icon' => 'map-pin', 'permission' => 'view_site_visits'],
                    ['label' => 'Clients', 'route' => 'clients.index', 'active' => 'clients.*', 'icon' => 'users', 'permission' => 'view_clients'],
                    ['label' => 'Meetings', 'route' => 'meetings.index', 'active' => 'meetings.*', 'icon' => 'calendar', 'permission' => 'view_meetings'],
                    ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => 'quotations.*', 'icon' => 'file-text', 'permission' => 'view_quotations'],
                    ['label' => 'Contracts', 'route' => 'contracts.index', 'active' => 'contracts.*', 'icon' => 'file-signature', 'permission' => 'view_contracts'],
                    ['label' => 'Change Orders', 'route' => 'change-orders.index', 'active' => 'change-orders.*', 'icon' => 'git-pull-request', 'permission' => 'view_change_orders'],
                ]],
                ['label' => 'Design', 'icon' => 'palette', 'children' => [
                    ['label' => 'Designs', 'route' => 'designs.index', 'active' => 'designs.*', 'icon' => 'palette', 'permission' => 'view_designs'],
                    ['label' => 'Material Selections', 'route' => 'material-selections.index', 'active' => 'material-selections.*', 'icon' => 'tag', 'permission' => 'view_material_selections'],
                ]],
            ],
        ],
        [
            'label' => 'Project Section',
            'entries' => [
                // Vendor Quotations / Goods Receives have no index route —
                // reached from a Purchase Request / Purchase Order.
                ['label' => 'Procurement', 'icon' => 'shopping-cart', 'children' => [
                    ['label' => 'Purchase Requests', 'route' => 'purchase-requests.index', 'active' => 'purchase-requests.*', 'icon' => 'file-plus', 'permission' => 'view_purchase_orders'],
                    ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'active' => 'purchase-orders.*', 'icon' => 'shopping-cart', 'permission' => 'view_purchase_orders'],
                    ['label' => 'Material Requisitions', 'route' => 'material-requisitions.index', 'active' => 'material-requisitions.*', 'icon' => 'clipboard-list', 'permission' => 'view_material_requisitions'],
                    ['label' => 'Products', 'route' => 'products.index', 'active' => 'products.*', 'icon' => 'package', 'permission' => 'view_inventory'],
                    ['label' => 'Warehouses', 'route' => 'warehouses.index', 'active' => 'warehouses.*', 'icon' => 'warehouse', 'permission' => 'view_warehouses'],
                    ['label' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'active' => 'stock-transfers.*', 'icon' => 'arrow-left-right', 'permission' => 'view_warehouses'],
                    ['label' => 'Stock Movements', 'route' => 'stock-movements.index', 'icon' => 'list', 'permission' => 'view_inventory'],
                ]],
                ['label' => 'Vendor', 'icon' => 'truck', 'children' => [
                    ['label' => 'List', 'route' => 'vendors.index', 'active' => 'vendors.*', 'icon' => 'truck', 'permission' => 'view_vendors'],
                    ['label' => 'Vendor Invoices', 'route' => 'vendor-invoices.index', 'active' => 'vendor-invoices.*', 'icon' => 'receipt', 'permission' => 'view_vendor_invoices'],
                ]],
                // Assignments are reached from a Subcontractor's page.
                ['label' => 'Contractor', 'icon' => 'hard-hat', 'children' => [
                    ['label' => 'List', 'route' => 'subcontractors.index', 'active' => 'subcontractors.*', 'icon' => 'hard-hat', 'permission' => 'view_subcontractors'],
                ]],
                // Milestones/Tasks/Team and Handovers live inside Project Show.
                ['label' => 'Project', 'icon' => 'briefcase', 'children' => [
                    ['label' => 'Projects', 'route' => 'projects.index', 'active' => 'projects.*', 'icon' => 'briefcase', 'permission' => 'view_projects'],
                    ['label' => 'BOQs', 'route' => 'boqs.index', 'active' => 'boqs.*', 'icon' => 'calculator', 'permission' => 'view_boqs'],
                    ['label' => 'Site Reports', 'route' => 'site-reports.index', 'active' => 'site-reports.*', 'icon' => 'file-plus', 'permission' => 'view_site_reports'],
                    ['label' => 'Inspections', 'route' => 'inspections.index', 'active' => 'inspections.*', 'icon' => 'clipboard-check', 'permission' => 'view_inspections'],
                    ['label' => 'Snags', 'route' => 'snags.index', 'active' => 'snags.*', 'icon' => 'alert-triangle', 'permission' => 'view_snags'],
                    ['label' => 'Warranties', 'route' => 'warranties.index', 'active' => 'warranties.*', 'icon' => 'shield-check', 'permission' => 'view_warranties'],
                ]],
            ],
        ],
        [
            'label' => 'Accounts Section',
            'entries' => [
                ['label' => 'Accounts', 'icon' => 'calculator', 'children' => [
                    ['label' => 'Invoices', 'route' => 'invoices.index', 'active' => 'invoices.*', 'icon' => 'receipt', 'permission' => 'view_invoices'],
                    ['label' => 'Payments', 'route' => 'payments.index', 'active' => 'payments.*', 'icon' => 'credit-card', 'permission' => 'view_payments'],
                    ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => 'expenses.*', 'icon' => 'wallet', 'permission' => 'view_expenses'],
                    ['label' => 'Expense Categories', 'route' => 'expense-categories.index', 'active' => 'expense-categories.*', 'icon' => 'folder', 'permission' => 'manage_settings'],
                ]],
            ],
        ],
        [
            'label' => 'HR Section',
            'entries' => [
                ['label' => 'Employees', 'icon' => 'users', 'children' => [
                    ['label' => 'List', 'route' => 'employees.index', 'active' => 'employees.*', 'icon' => 'users', 'permission' => 'view_employees'],
                    ['label' => 'Departments', 'route' => 'departments.index', 'active' => 'departments.*', 'icon' => 'building', 'permission' => 'manage_employees'],
                    ['label' => 'Designations', 'route' => 'designations.index', 'active' => 'designations.*', 'icon' => 'id-badge', 'permission' => 'manage_employees'],
                ]],
                ['label' => 'Attendance & Payroll', 'icon' => 'clock', 'children' => [
                    ['label' => 'Attendance', 'route' => 'attendance.index', 'active' => 'attendance.*', 'icon' => 'clock', 'permission' => 'view_attendance'],
                    ['label' => 'Leaves', 'route' => 'leaves.index', 'active' => 'leaves.*', 'icon' => 'calendar-off', 'permission' => 'view_leaves'],
                    ['label' => 'Expense Claims', 'route' => 'expense-claims.index', 'active' => 'expense-claims.*', 'icon' => 'wallet', 'permission' => 'view_expense_claims'],
                    ['label' => 'Payroll', 'route' => 'payroll.index', 'active' => 'payroll.*', 'icon' => 'banknote', 'permission' => 'view_payroll'],
                ]],
                // Self-service: any staff login, checked inside each page
                // against "is this your own Employee record".
                ['label' => 'My Workspace', 'icon' => 'id-badge', 'children' => [
                    ['label' => 'My Leaves', 'route' => 'my-leaves.index', 'active' => 'my-leaves.*', 'icon' => 'calendar-off'],
                    ['label' => 'My Expense Claims', 'route' => 'my-expense-claims.index', 'active' => 'my-expense-claims.*', 'icon' => 'map-pin'],
                ]],
            ],
        ],
        [
            'label' => 'Report Section',
            'entries' => [
                ['label' => 'Accounts Report', 'icon' => 'bar-chart', 'children' => [
                    ['label' => 'Financial Summary', 'route' => 'reports.financial-summary', 'icon' => 'bar-chart', 'permission' => 'view_reports'],
                    ['label' => 'Profitability', 'route' => 'profitability.index', 'icon' => 'trending-up', 'permission' => 'view_finance'],
                ]],
                ['label' => 'CRM Report', 'icon' => 'funnel', 'children' => [
                    ['label' => 'Sales Funnel', 'route' => 'reports.sales-funnel', 'icon' => 'funnel', 'permission' => 'view_reports'],
                ]],
                ['label' => 'All Report', 'icon' => 'list', 'children' => [
                    ['label' => 'All Reports', 'route' => 'reports.index', 'icon' => 'list', 'permission' => 'view_reports'],
                    ['label' => 'Project Status', 'route' => 'reports.project-status', 'icon' => 'briefcase', 'permission' => 'view_reports'],
                    ['label' => 'Inventory', 'route' => 'reports.inventory', 'icon' => 'package', 'permission' => 'view_reports'],
                    ['label' => 'HR', 'route' => 'reports.hr', 'icon' => 'users', 'permission' => 'view_reports'],
                    ['label' => 'Audit Log', 'route' => 'audit-log.index', 'icon' => 'history', 'permission' => 'view_reports'],
                ]],
            ],
        ],
        [
            'label' => 'System',
            'entries' => [
                ['label' => 'Settings', 'icon' => 'settings', 'children' => [
                    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'permission' => 'manage_settings'],
                    ['label' => 'Users', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'users', 'permission' => 'view_users'],
                    ['label' => 'Roles', 'route' => 'roles.index', 'active' => 'roles.*', 'icon' => 'shield', 'permission' => 'view_roles'],
                    ['label' => 'Product Categories', 'route' => 'product-categories.index', 'active' => 'product-categories.*', 'icon' => 'tag', 'permission' => 'manage_settings'],
                    ['label' => 'Units', 'route' => 'units.index', 'active' => 'units.*', 'icon' => 'ruler', 'permission' => 'manage_settings'],
                ]],
            ],
        ],
    ];

    $user = auth()->user();
    $canSee = fn (array $link) => ! isset($link['permission']) || $user?->can($link['permission']);
    $isActive = fn (array $link) => request()->routeIs($link['active'] ?? $link['route'].'*');

    // Resolve visibility once: drop hidden links, empty dropdowns and
    // empty sections.
    $sections = collect($sections)->map(function ($section) use ($canSee) {
        $section['entries'] = collect($section['entries'])->map(function ($entry) use ($canSee) {
            if (isset($entry['children'])) {
                $entry['children'] = array_values(array_filter($entry['children'], $canSee));

                return $entry['children'] ? $entry : null;
            }

            return $canSee($entry) ? $entry : null;
        })->filter()->values()->all();

        return $section;
    })->filter(fn ($section) => $section['entries'])->values();
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

    Menu sections and dropdowns are defined in $sections above. In the
    collapsed rail each dropdown shows as its icon (solid when it holds
    the current page); clicking it expands the menu with that dropdown
    open.
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
        @foreach ($sections as $section)
            @if ($section['label'])
                <p x-show="! rail" class="px-3 pb-1.5 pt-4 text-[11px] font-bold uppercase tracking-[0.1em] text-ink-400">{{ $section['label'] }}</p>
            @endif
            @unless ($loop->first)
                <div x-show="rail" x-cloak class="mx-auto my-2 h-1 w-1 rounded-full bg-ink-300"></div>
            @endunless

            <div class="space-y-1">
                @foreach ($section['entries'] as $entry)
                    @if (isset($entry['children']))
                        @php
                            $childActive = collect($entry['children'])->contains(fn ($c) => $isActive($c));
                            $storageKey = 'sidebar-menu-open:'.\Illuminate\Support\Str::slug(($section['label'] ?? 'top').'-'.$entry['label']);
                        @endphp
                        <div
                            x-data="{
                                open: {{ $childActive ? 'true' : 'false' }},
                                key: @js($storageKey),
                                init() {
                                    // The menu holding the current page always
                                    // starts open; otherwise the last choice wins.
                                    if (! this.open) {
                                        try { this.open = localStorage.getItem(this.key) === '1'; } catch (e) {}
                                    }
                                },
                                toggle() {
                                    if (rail) { toggleCollapsed(); this.open = true; }
                                    else { this.open = ! this.open; }
                                    try { localStorage.setItem(this.key, this.open ? '1' : '0'); } catch (e) {}
                                },
                            }"
                        >
                            <button
                                type="button"
                                @click="toggle()"
                                aria-label="{{ $entry['label'] }}"
                                :aria-expanded="open && ! rail"
                                @mouseenter="showTip($el, @js($entry['label']))"
                                @mouseleave="tip.show = false"
                                @class([
                                    'group/nav flex w-full items-center rounded-full text-[13.5px] transition-colors',
                                    'font-semibold text-ink-900' => $childActive,
                                    'font-medium text-ink-600 hover:bg-ink-100 hover:text-ink-900' => ! $childActive,
                                ])
                                :class="rail
                                    ? 'mx-auto h-11 w-11 justify-center {{ $childActive ? '!bg-charcoal !text-white dark:!bg-accent-500 dark:!text-charcoal' : '' }}'
                                    : 'gap-3 px-3.5 py-2.5'"
                            >
                                <x-icon
                                    :name="$entry['icon']"
                                    @class([
                                        'h-[18px] w-[18px] shrink-0',
                                        'text-accent-600 dark:text-accent-400' => $childActive,
                                        'text-ink-400 group-hover/nav:text-ink-700' => ! $childActive,
                                    ])
                                    x-bind:class="rail && {{ $childActive ? 'true' : 'false' }} ? '!text-accent-500 dark:!text-charcoal' : ''"
                                />
                                <span class="flex-1 truncate text-left" x-show="! rail">{{ $entry['label'] }}</span>
                                <x-icon
                                    name="chevron-down"
                                    class="h-4 w-4 shrink-0 text-ink-400 transition-transform duration-150"
                                    x-show="! rail"
                                    x-bind:class="open ? 'rotate-180' : ''"
                                />
                            </button>

                            <div x-show="open && ! rail" x-cloak x-collapse.duration.150ms class="relative ml-[22px] mt-1 space-y-0.5 border-l border-ink-200 pl-3 dark:border-white/10">
                                @foreach ($entry['children'] as $child)
                                    @php $active = $isActive($child); @endphp
                                    <a
                                        href="{{ route($child['route']) }}"
                                        wire:navigate
                                        @if ($active) aria-current="page" @endif
                                        @class([
                                            'group/sub flex items-center gap-2.5 rounded-full px-3 py-2 text-[13px] transition-colors',
                                            'bg-charcoal font-semibold text-white dark:bg-accent-500 dark:text-charcoal' => $active,
                                            'font-medium text-ink-600 hover:bg-ink-100 hover:text-ink-900' => ! $active,
                                        ])
                                    >
                                        <x-icon
                                            :name="$child['icon']"
                                            @class([
                                                'h-4 w-4 shrink-0',
                                                'text-accent-500 dark:text-charcoal' => $active,
                                                'text-ink-400 group-hover/sub:text-ink-700' => ! $active,
                                            ])
                                        />
                                        <span class="truncate">{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php $active = $isActive($entry); @endphp
                        <a
                            href="{{ route($entry['route']) }}"
                            wire:navigate
                            aria-label="{{ $entry['label'] }}"
                            @if ($active) aria-current="page" @endif
                            @mouseenter="showTip($el, @js($entry['label']))"
                            @mouseleave="tip.show = false"
                            @class([
                                'group/nav flex items-center rounded-full text-[13.5px] transition-colors',
                                'bg-charcoal font-semibold text-white dark:bg-accent-500 dark:text-charcoal' => $active,
                                'font-medium text-ink-600 hover:bg-ink-100 hover:text-ink-900' => ! $active,
                            ])
                            :class="rail ? 'mx-auto h-11 w-11 justify-center' : 'gap-3 px-3.5 py-2.5'"
                        >
                            <x-icon
                                :name="$entry['icon']"
                                @class([
                                    'h-[18px] w-[18px] shrink-0',
                                    'text-accent-500 dark:text-charcoal' => $active,
                                    'text-ink-400 group-hover/nav:text-ink-700' => ! $active,
                                ])
                            />
                            <span class="truncate" x-show="! rail">{{ $entry['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
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
