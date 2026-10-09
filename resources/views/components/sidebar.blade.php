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
        // Phase 3+: Projects, Design, BOQ, Quotations, Contracts, Inventory,
        // Procurement, HR, Reports...
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
