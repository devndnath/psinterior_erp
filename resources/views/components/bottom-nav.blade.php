{{--
    App-style bottom tab bar for phones and tablets (hidden from lg up,
    where the sidebar is always on screen). The first four tabs the user
    may open from the list below, then "Menu", which slides out the full
    sidebar. Sits above the home indicator on notched phones
    (safe-area inset). The layout pads <main> so content never hides
    behind it.
--}}
@php
    $user = auth()->user();
    $candidates = [
        ['Home', 'dashboard', 'layout-dashboard', null, ['dashboard']],
        ['CRM', 'crm.overview', 'funnel', ['view_leads'], ['crm.*', 'leads.*', 'clients.*', 'site-visits.*', 'references.*', 'quotations.*']],
        ['Calls', 'calls.index', 'phone', ['view_leads', 'view_clients'], ['calls.*']],
        ['Projects', 'projects.index', 'briefcase', ['view_projects'], ['projects.*', 'designs.*', 'boqs.*']],
        ['Accounts', 'invoices.index', 'receipt', ['view_invoices'], ['invoices.*', 'payments.*', 'expenses.*']],
        ['Team', 'employees.index', 'users', ['view_employees'], ['employees.*', 'attendance.*', 'payroll.*', 'leaves.*']],
        ['Calendar', 'calendar.index', 'calendar', null, ['calendar.*']],
        ['Claims', 'my-expense-claims.index', 'wallet', null, ['my-expense-claims.*']],
    ];
    $tabs = collect($candidates)
        ->filter(fn ($t) => \Illuminate\Support\Facades\Route::has($t[1]) && ($t[3] === null || $user?->canAny($t[3])))
        ->take(4);
@endphp

<nav class="bottom-nav fixed inset-x-0 bottom-0 z-30 border-t border-ink-200/70 bg-surface/95 backdrop-blur-md lg:hidden dark:border-white/[0.06]" aria-label="Main">
    <div class="mx-auto flex max-w-xl items-stretch justify-around px-1">
        @foreach ($tabs as [$label, $route, $icon, $permission, $active])
            @php($on = request()->routeIs(...$active))
            <a href="{{ route($route) }}" wire:navigate @if ($on) aria-current="page" @endif
               class="bottom-nav-tab {{ $on ? 'is-active' : '' }}">
                <span class="bottom-nav-icon">{!! \App\Support\Icon::svg($icon, 'h-[22px] w-[22px]') !!}</span>
                <span>{{ $label }}</span>
            </a>
        @endforeach
        <button type="button" @click="sidebarOpen = true" class="bottom-nav-tab" :class="sidebarOpen && 'is-active'" aria-label="Open the full menu">
            <span class="bottom-nav-icon">{!! \App\Support\Icon::svg('layout-grid', 'h-[22px] w-[22px]') !!}</span>
            <span>Menu</span>
        </button>
    </div>
</nav>
